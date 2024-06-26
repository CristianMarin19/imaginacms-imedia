<?php

namespace Modules\Media\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use Modules\Media\Entities\File;
use Modules\Media\Events\FileIsCreating;
use Modules\Media\Events\FileIsUpdating;
use Modules\Media\Events\FileStartedMoving;
use Modules\Media\Events\FileWasCreated;
use Modules\Media\Events\FileWasUpdated;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Repositories\FileRepository;
use Modules\Media\Repositories\FolderRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Modules\Core\Icrud\Repositories\Eloquent\EloquentCrudRepository;

class EloquentFileRepository extends EloquentCrudRepository implements FileRepository
{

  /**
   * Filter names to replace
   * @var array
   */
  protected $replaceFilters = ["folderId","extension"];

  /**
   * Relation names to replace
   * @var array
   */
  protected $replaceSyncModelRelations = [];

  /**
   * Attribute to customize relations by default
   * @var array
   */
  protected $with = [
    'index' => [ 'tags'],
    'show' => [ 'tags'],
  ];


  /**
   * Filter query
   *
   * @param $query
   * @param $filter
   * @param $params
   * @return mixed
   */
  public function filterQuery($query, $filter, $params)
  {

    /**
     * Note: Add filter name to replaceFilters attribute before replace it
     *
     * Example filter Query
     * if (isset($filter->status)) $query->where('status', $filter->status);
     *
     */

    if (isset($filter->folderId) && (string)$filter->folderId != "") {
      $query->where('folder_id', $filter->folderId);

    }

    if (!isset($params->permissions['media.medias.index']) ||
      (isset($params->permissions['media.medias.index']) &&
        !$params->permissions['media.medias.index'])) {
      $query->where("is_folder", "!=", 0);
    }


    if (!isset($params->permissions['media.folders.index']) ||
      (isset($params->permissions['media.folders.index']) &&
        !$params->permissions['media.folders.index'])) {
      $query->where("is_folder", "!=", 1);
    }

    //folder name
    if (isset($filter->folderName) && $filter->folderName != "Home") {

      $folder = \DB::table("media__files as files")
        ->where("is_folder", true)
        ->where("filename", $filter->folderName)
        ->first();

      if (isset($folder->id)) {
        $query->where('folder_id', $filter->folderId ?? $folder->id);
      }
    }

    //is Folder
    if (isset($filter->isFolder)) {
      $query->where('is_folder', $filter->isFolder ?? 0);
    }

    //Zone
    if (isset($filter->zone)) {
      $filesByZoneIds = \DB::table("media__imageables as imageable")
        ->where('imageable.zone', $filter->zone)
        ->where('imageable.imageable_id', $filter->entityId)
        ->where('imageable.imageable_type', $filter->entity)
        ->orderBy('order', 'asc')
        ->get()->pluck("file_id")->toArray();


      $query->whereIn("id", $filesByZoneIds);
    }

    //Entity Type
    if (isset($filter->entity) && !isset($filter->zone)) {
      $filesByEntity = \DB::table("media__imageables as imageable")
        ->where('imageable.imageable_type', $filter->entity)
        ->orderBy('order', 'asc')
        ->get()->pluck("file_id")->toArray();

      $query->whereIn("id", $filesByEntity);
    }


    //add filter by search
    if (isset($filter->search) && $filter->search) {
      //find search in columns
      $query->where(function ($query) use ($filter) {
        $query->whereHas('translations', function ($query) use ($filter) {
          $query->where('description', 'like', "%{$filter->search}%")
            ->orWhere('alt_attribute', 'like', "%{$filter->search}%");
        });
      })->orWhere('id', 'like', '%' . $filter->search . '%')
        ->orWhere('filename', 'like', '%' . $filter->search . '%')
        ->orWhere('updated_at', 'like', '%' . $filter->search . '%')
        ->orWhere('created_at', 'like', '%' . $filter->search . '%');

    }

    //Filter by extension
    if (isset($filter->extension)) {
      if (!is_array($filter->extension)) $filter->extension = [$filter->extension];
      $query->whereIn('extension', $filter->extension);
    }

    $settingFilesystem = setting('media::filesystem', null, config("asgard.media.config.filesystem"));
    //Getting the disk name
    $filterDisk = isset($params->filter) && isset($params->filter->disk) ? $params->filter->disk : $settingFilesystem;

    //Validate disk name
    $filterDisk = in_array($filterDisk, array_keys(config('filesystems.disks'))) ? $filterDisk : $settingFilesystem;

    //verify if the frontend send publicmedia and the default disk is s3 automatically do switch to s3
    //esto porque Michael pidió una solución rápida para no afectar front fuertemente en una primera versión
    if ($filterDisk == "publicmedia" && $settingFilesystem == "s3") $filterDisk = $settingFilesystem;

    //igualmente para privatemedia
    if ($filterDisk == "privatemedia" && $settingFilesystem == "s3") $filterDisk = $settingFilesystem;

    //Filter by disk name
    if ($filterDisk == $settingFilesystem) {
      $query->where(function ($q) use ($filterDisk) {
        $q->where("disk", $filterDisk)
          ->orWhereNull("disk");
      });
    } else {
      $query->where("disk", $filterDisk);
    }


    $this->validateIndexAllPermission($query, $params);

    //Response
    return $query;
  }


  /**
   * Method to sync Model Relations
   *
   * @param $model ,$data
   * @return $model
   */
  public function syncModelRelations($model, $data)
  {
    //Get model relations data from attribute of model
    $modelRelationsData = ($model->modelRelations ?? []);

    /**
     * Note: Add relation name to replaceSyncModelRelations attribute before replace it
     *
     * Example to sync relations
     * if (array_key_exists(<relationName>, $data)){
     *    $model->setRelation(<relationName>, $model-><relationName>()->sync($data[<relationName>]));
     * }
     *
     */

    //Response
    return $model;
  }

  /**
   * Update a resource
   * @param File $file
   * @param $data
   * @return mixed
   */
  public function update($file, $data)
  {
    event($event = new FileIsUpdating($file, $data));
    $file->update($event->getAttributes());

    event(new FileWasUpdated($file));

    return $file;
  }

  /**
   * Create a file row from the given file
   * @param UploadedFile $file
   * @param int $parentId
   * @param string $disk
   * @return mixed
   */
  public function createFromFile(UploadedFile $file, $parentId = 0, $disk = null)
  {
    $fileName = FileHelper::slug($file->getClientOriginalName());

    $existFile = $this->model->where('filename', $fileName)
      ->where('extension', substr(strrchr($fileName, '.'), 1))
      ->where('filesize', $file->getFileInfo()->getSize())
      ->where('is_folder', 0)
      ->first();

    if ($existFile) return $existFile;

    $exists = $this->model->where('filename', $fileName)->where('folder_id', $parentId)->where('disk', $disk)->first();

        if ($exists) {
            $fileName = $this->getNewUniqueFilename($fileName);
        }

        $data = [
            'filename' => $fileName,
            'path' => $this->getPathFor($fileName, $parentId),
            'extension' => substr(strrchr($fileName, '.'), 1),
            'mimetype' => $file->getClientMimeType(),
            'filesize' => $file->getFileInfo()->getSize(),
            'folder_id' => $parentId ?? 0,
            'is_folder' => 0,
            'disk' => $disk,
        ];

        event($event = new FileIsCreating($data));

        $file = $this->model->create($event->getAttributes());
        event(new FileWasCreated($file));

        return $file;
    }

    private function getPathFor(string $filename, $folderId)
    {
        if ($folderId !== 0) {
            $parent = app(FolderRepository::class)->findFolder($folderId);
            if ($parent !== null) {
                return $parent->path->getRelativeUrl().'/'.$filename;
            }
        }

        return config('asgard.media.config.files-path').$filename;
    }

    public function destroy($file)
    {
        $file->delete();
    }

    /**
     * Find a file for the entity by zone
     */
    public function findFileByZoneForEntity($zone, $entity)
    {
        foreach ($entity->files as $file) {
            if ($file->pivot->zone == $zone) {
                return $file;
            }
        }

        return '';
    }

    /**
     * Find multiple files for the given zone and entity
     */
    public function findMultipleFilesByZoneForEntity($zone, $entity)
    {
        $files = [];
        foreach ($entity->files as $file) {
            if ($file->pivot->zone == $zone) {
                $files[] = $file;
            }
        }

        return new Collection($files);
    }

    /**
     * @return string
     */
    private function getNewUniqueFilename($fileName)
    {
        $fileNameOnly = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $models = $this->model->where('filename', 'LIKE', "$fileNameOnly%")->get();

        $versionCurrent = $models->reduce(function ($carry, $model) {
            $latestFilename = pathinfo($model->filename, PATHINFO_FILENAME);

            if (preg_match('/_([0-9]+)$/', $latestFilename, $matches) !== 1) {
                return $carry;
            }

            $version = (int) $matches[1];

            return ($version > $carry) ? $version : $carry;
        }, 0);

        return $fileNameOnly.'_'.($versionCurrent + 1).'.'.$extension;
    }

    /**
     * @return mixed
     */
    public function serverPaginationFilteringFor(Request $request)
    {
        $media = $this->allWithBuilder();

        $media->orderBy('is_folder', 'desc');
        $media->where('folder_id', $request->get('folder_id', 0));

        if ($request->get('search') !== null) {
            $term = $request->get('search');
            $media->where('filename', 'LIKE', "%{$term}%");
        }

        if ($request->get('order_by') !== null && $request->get('order') !== 'null') {
            $order = $request->get('order') === 'ascending' ? 'asc' : 'desc';

            $media->orderBy($request->get('order_by'), $order);
        } else {
            $media->orderBy('created_at', 'desc');
        }

        return $media->paginate($request->get('per_page', 10));
    }

    /**
     * @param  int  $folderId
     */
    public function allChildrenOf($folderId): Collection
    {
        return $this->model->where('folder_id', $folderId)->get();
    }

    public function findForVirtualPath($criteria, $params = [])
    {
        $query = $this->model->where('id', $criteria);

        $this->validateIndexAllPermission($query, $params);

        return $query->first();
    }

    public function allForGrid(): Collection
    {
        return $this->model->where('is_folder', 0)->get();
    }

    public function move(File $file, File $destination): File
    {
        $previousData = [
            'filename' => $file->filename,
            'path' => $file->path,
        ];

        $this->update($file, [
            'path' => $this->getPathFor($file->filename, $destination->id),
            'folder_id' => $destination->id,
        ]);

        event(new FileStartedMoving($file, $previousData));

        return $file;
    }


  public function getItemsBy($params = false)
  {

    $response = parent::getItemsBy($params);

    if(isset($params->returnAsQuery) && $params->returnAsQuery) {
      return $response;}

    $filter = $params->filter;

    //Order if filter by zone
    if (isset($filter->zone)) {

      $filesByZoneIds = \DB::table("media__imageables as imageable")
        ->where('imageable.zone', $filter->zone)
        ->where('imageable.imageable_id', $filter->entityId)
        ->where('imageable.imageable_type', $filter->entity)
        ->orderBy('order', 'asc')
        ->get()->pluck("file_id")->toArray();

      //Sort response collection
      $sorted = $response->sortBy(function ($model) use ($filesByZoneIds) {
        return array_search($model->getKey(), $filesByZoneIds);
      });
      //Replace response collection
      if (isset($params->page) && $params->page) $response->setCollection($sorted);
      else $response = $sorted;
    }

    //Response
    return $response;
  }


  public function getItem($criteria, $params = false)
  {
    //Initialize query
    $query = $this->model->query();

        /*== RELATIONSHIPS ==*/
        if (in_array('*', $params->include ?? [])) {//If Request all relationships
            $query->with([]);
        } else {//Especific relationships
            $includeDefault = []; //Default relationships
            if (isset($params->include)) {//merge relations with default relationships
                $includeDefault = array_merge($includeDefault, $params->include);
            }
            $query->with($includeDefault); //Add Relationships to query
        }

        /*== FILTER ==*/
        if (isset($params->filter)) {
            $filter = $params->filter;

            if (isset($filter->field)) {//Filter by specific field
                $field = $filter->field;
            }

            //is Folder
            if (isset($filter->zone)) {
                $filesIds = \DB::table('media__imageables as imageable')
                  ->where('imageable.zone', $filter->zone)
                  ->where('imageable.imageable_id', $filter->entityId)
                  ->where('imageable.imageable_type', $filter->entity)
                  ->get()->pluck('file_id')->toArray();
                $query->whereIn('id', $filesIds);
            }
        }

        /*== FIELDS ==*/
        if (isset($params->fields) && count($params->fields)) {
            $query->select($params->fields);
        }

        /*== REQUEST ==*/
        return $query->where($field ?? 'id', $criteria)->first();
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function updateBy($criteria, $data, $params = false)
    {
        /*== initialize query ==*/
        $query = $this->model->query();

        /*== FILTER ==*/
        if (isset($params->filter)) {
            $filter = $params->filter;

            //Update by field
            if (isset($filter->field)) {
                $field = $filter->field;
            }
        }
        /*== REQUEST ==*/
        $model = $query->where($field ?? 'id', $criteria)->first();

        if ($model) {
            //$model->update((array)$data);

            event($event = new FileIsUpdating($model, $data));
            $model->update($event->getAttributes());

      event(new FileWasUpdated($model));

    }
  }


    public function deleteBy($criteria, $params = false)
    {
        /*== initialize query ==*/
        $query = $this->model->query();

        /*== FILTER ==*/
        if (isset($params->filter)) {
            $filter = $params->filter;

            if (isset($filter->field)) {//Where field
                $field = $filter->field;
            }
        }

        /*== REQUEST ==*/
        $model = $query->where($field ?? 'id', $criteria)->first();

        if (isset($model->id)) {
            $model->delete();
            $model->forceDelete();
        }
    }

  function validateIndexAllPermission(&$query, $params)
  {
    // filter by permission: index all leads

    if (!isset($params->permissions['media.medias.index-all']) ||
      (isset($params->permissions['media.medias.index-all']) &&
        !$params->permissions['media.medias.index-all'])) {
      $user = $params->user ?? null;

      $role = $params->role ?? null;
      // if is salesman or salesman manager or salesman sub manager
      $query->where('created_by', $user->id ?? null);


    }
  }
}
