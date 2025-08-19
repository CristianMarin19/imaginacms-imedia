<?php

namespace Modules\Imedia\Support;

class ImageHelper
{

    public static function encodeImage($image, $extension, $quality)
    {

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $encoder = new \Intervention\Image\Encoders\JpegEncoder(quality: $quality);
                break;
            case 'png':
                $encoder = new \Intervention\Image\Encoders\PngEncoder();
                break;
            case 'webp':
                $encoder = new \Intervention\Image\Encoders\WebpEncoder(quality: $quality);
                break;
        }

        return $image->encode($encoder);
    }
}
