<?php

return [

    /*
  |--------------------------------------------------------------------------
  | The path where the media files will be uploaded
  |--------------------------------------------------------------------------
  | Note: Trailing slash is required
  */
    'files-path' => '/assets/media/',
    /*
  |--------------------------------------------------------------------------
  | Specify all file extensions that do not require resizing images and creating thumbnails.
  |--------------------------------------------------------------------------
  */
    'typesWithoutResizeImagesAndCreateThumbnails' => ['gif'],
    /*
  |--------------------------------------------------------------------------
  | Specify all the allowed file extensions a user can upload on the server
  |--------------------------------------------------------------------------
  */
    'allowed-types' => 'jpg,png,pdf,jpeg,mp4,webm,ogg,svg,webp',
    'allowedImageTypes' => json_encode(['jpg', 'png', 'jpeg', 'webp']),
    'allowedFileTypes' => json_encode(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'svg']),
    'allowedVideoTypes' => json_encode(['mp4', 'webm', 'ogg']),
    'allowedAudioTypes' => json_encode(['mp3', 'avi']),
    /*
  |--------------------------------------------------------------------------
  | default to Resize
  |--------------------------------------------------------------------------
  */
    'defaultImageSize' => json_encode(['width' => 1920, 'height' => 1920, 'quality' => 90]),

    /*
  |--------------------------------------------------------------------------
  | default to Thumbnails
  |--------------------------------------------------------------------------
  */
    'defaultThumbnails' => json_encode([
        'smallThumb' => [
            'quality' => 80,
            'width' => 300,
            'height' => null,
            'aspectRatio' => true,
            'upsize' => true,
            'format' => 'webp',
        ],
        'mediumThumb' => [
            'quality' => 80,
            'width' => 600,
            'height' => null,
            'aspectRatio' => true,
            'upsize' => true,
            'format' => 'webp',
        ],
        'largeThumb' => [
            'quality' => 80,
            'width' => 900,
            'height' => null,
            'aspectRatio' => true,
            'upsize' => true,
            'format' => 'webp',
        ],
        'extraLargeThumb' => [
            'quality' => 80,
            'width' => 1920,
            'height' => null,
            'aspectRatio' => true,
            'upsize' => true,
            'format' => 'webp',
        ],
    ]),
];
