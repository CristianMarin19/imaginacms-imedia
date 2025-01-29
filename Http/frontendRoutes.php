<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;

/** @var Router $router */
$router->get('/assets/media/{route?}/{criteria}', [
  'as' => 'public.media.media.show',
  'uses' => 'Frontend\MediaController@show',
])->where('route', '.*');
/*
$router->get('storage/assets/media/{path}',[
    'as' => 'public.media.media.show',
    'uses' => 'Frontend\MediaController@show',
]);*/
