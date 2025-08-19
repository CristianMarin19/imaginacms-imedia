<?php

namespace Modules\Imedia\Services;


use Validator;
use Illuminate\Support\Facades\Storage;

use Modules\Imedia\Repositories\FileRepository;
use Modules\Imedia\Support\FileHelper;

class FolderService
{

    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param File
     */
    public function store($data)
    {

        //Validations
        $this->checkValidations($data);

        //Get Disk
        $disk = $data['disk'] ?? 's3';
        $parentId = $data['parent_id'] ?? null;

        //Save Folder
        $file = $this->saveData($data, $disk, $parentId);

        //Save in disk
        Storage::disk($disk)->makeDirectory($file->path, [
            'visibility' => $file['visibility']
        ]);

        //Return File
        return $file;
    }


    /**
     * Validations
     */
    private function checkValidations($data)
    {
        $request = new \Modules\Imedia\Http\Requests\CreateFolderRequest($data);
        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
        //if get errors, throw errors
        if ($validator->fails()) {
            throw new \Exception(json_encode($validator->errors()), 400);
        }
    }

    /**
     * Save Data in Database
     */
    private function saveData($data, $disk, $parentId)
    {

        $filename = $data['name'];

        //Fix data to Save
        $dataToSave = [
            'filename' => $filename,
            'path' => FileHelper::makePath($filename, $parentId),
            'folder_id' => $data['parent_id'] ?? null,
            'is_folder' => true,
            'disk' => $disk,
            'visibility' => $data['visibility'] ?? 'public'
        ];

        //Save File
        return $this->fileRepository->create($dataToSave);
    }
}
