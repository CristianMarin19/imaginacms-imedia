<?php


namespace Modules\Imedia\Support;

use Illuminate\Database\Eloquent\Collection;
use Modules\Imedia\Models\File;
use Modules\Imedia\Transformers\FileTransformer;

class FileCollection extends Collection
{

  /**
   * Relation used with transformers in Modules
   */
  public function byZones(array $zones = [], $resource = null): object
  {
    $classInfo = $this->getClassInfo($resource);
    $response = collect($zones)->mapWithKeys(function ($fileType, $fieldName) use ($classInfo) {
      $zone = strtolower($fieldName);

      $filesByZone = $this->filter(fn($item) => $item->pivot->zone === $zone);

      // No files? simulate with placeholder
      if ($filesByZone->isEmpty()) {
        $filesByZone = collect([0]);
      }

      // Transform files
      $transformed = $filesByZone
        ->map(fn($file) => $this->transformFile($file, $classInfo))
        ->filter(); // removes null/false if transformFile returns that

      // Single vs multiple
      $value = ($fileType === 'multiple')
        ? $transformed->values()->all()
        : $transformed->first();

      return [$zone => $value];
    });

    //Response
    return (object)$response->all();
  }

  /**
   * Get information about the class that use this trait
   */
  private function getClassInfo($resource): array
  {
    //Get Model
    $model = $resource->resource;

    $entityNamespace = get_class($model);
    $entityNamespaceExploded = explode('\\', strtolower($entityNamespace));

    return [
      "moduleName" => $entityNamespaceExploded[1],
      "entityName" => $entityNamespaceExploded[3],
    ];
  }

  /**
   *
   */
  public function transformFile($file, $classInfo, $defaultPath = null)
  {
    //Create a mockup of a file if not exist
    if (!$file) {
      if (!$defaultPath) {
        $defaultPath = strtolower("modules/{$classInfo["moduleName"]}/img/{$classInfo["entityName"]}/default.jpg");
      }

      $file = new File(['path' => $defaultPath, 'is_folder' => 0]);
    }

    return json_decode(json_encode(new FileTransformer($file)));
  }
}
