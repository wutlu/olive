<?php

namespace App\Utilities;

class ImageUtility
{
    public static function isImage(string $path)
    {
        $a = getimagesize($path);
        $image_type = $a[2];

        return in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)) ? true : false;
    }
}
