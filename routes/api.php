<?php

use Illuminate\Support\Facades\Route;
use Modules\Imedia\Http\Controllers\Api\FileApiController;
use Modules\Imedia\Http\Controllers\Api\ZoneApiController;
// add-use-controller



Route::prefix('/imedia/v1')->group(function () {
    Route::apiCrud([
      'module' => 'imedia',
      'prefix' => 'files',
      'controller' => FileApiController::class,
      'permission' => 'imedia.files',
      //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
      // 'customRoutes' => [ // Include custom routes if needed
      //  [
      //    'method' => 'post', // get,post,put....
      //    'path' => '/some-path', // Route Path
      //    'uses' => 'ControllerMethodName', //Name of the controller method to use
      //    'middleware' => [] // if not set up middleware, auth:api will be the default
      //  ]
      // ]
    ]);
    Route::apiCrud([
      'module' => 'imedia',
      'prefix' => 'zones',
      'controller' => ZoneApiController::class,
      'permission' => 'imedia.zones',
      //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
      // 'customRoutes' => [ // Include custom routes if needed
      //  [
      //    'method' => 'post', // get,post,put....
      //    'path' => '/some-path', // Route Path
      //    'uses' => 'ControllerMethodName', //Name of the controller method to use
      //    'middleware' => [] // if not set up middleware, auth:api will be the default
      //  ]
      // ]
    ]);
// append


});
