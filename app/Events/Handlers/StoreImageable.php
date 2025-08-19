<?php

namespace Modules\Imedia\Events\Handlers;

use Illuminate\Support\Arr;

class StoreImageable
{
  public function handle($event): void
  {
    // All params from the Event
    $params = $event->params;

    // Get specific data
    $dataFromRequest = $params['data'];
    $model = $params['model'];

    // Handle single media
    if (!empty($dataFromRequest['medias_single'])) {
      $this->handleSingleMedia($dataFromRequest, $model);
    }

    // Handle multi media
    if (!empty($dataFromRequest['medias_multi'])) {
      $this->handleMultiMedia($dataFromRequest, $model);
    }
  }

  /**
   * Handle the request to parse single media partials
   */
  private function handleSingleMedia($data, $model): void
  {

    $mediaData = $data['medias_single'] ?? [];

    foreach ($mediaData as $zone => $fileId) {
      if (!empty($fileId)) {
        //$model->filesByZone($zone)->sync([$fileId => ['imageable_type' => get_class($model), 'zone' => $zone, 'order' => null]]);
        $model->files()->wherePivot('zone', '=', $zone)->sync([$fileId => ['imageable_type' => get_class($model), 'zone' => $zone, 'order' => null]]);
      } else {
        $model->files()->wherePivot('zone', '=', $zone)->sync([]);
      }
    }
  }

  /**
   * Handle the request for the multimedia partial
   */
  private function handleMultiMedia($data, $model): void
  {
    $mediaData = $data['medias_multi'] ?? [];

    foreach ($mediaData as $zone => $attributes) {
      $syncList = [];
      $orders = $this->getOrdersFrom($attributes);

      $files = Arr::get($attributes, 'files', []);

      foreach ($files as $fileId) {
        $syncList[$fileId] = [];
        $syncList[$fileId]['imageable_type'] = get_class($model);
        $syncList[$fileId]['zone'] = $zone;
        $syncList[$fileId]['order'] = (int)array_search($fileId, $orders);
      }

      $model->files()->wherePivot('zone', '=', $zone)->sync($syncList);
    }
  }

  /**
   * Parse the order input and return an array of file ids, in order
   * @param array $attributes
   * @return array
   */
  private function getOrdersFrom(array $attributes): array
  {
    $orderString = Arr::get($attributes, 'orders');

    if ($orderString === null) {
      return [];
    }

    $orders = explode(',', $orderString);

    return array_filter($orders);
  }
}
