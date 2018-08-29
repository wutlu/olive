<?php

namespace App\Utilities;

use App\Models\Log;

class SystemUtility
{
    # system activity
    public static function log(string $message, string $module, int $level = 1)
    {
        $uuid = md5(implode('-', [ $message, $module ]));

        $log = Log::updateOrCreate(
            [
                'uuid' => $uuid
            ],
            [
                'message' => $message,
                'module' => $module,
                'level' => $level
            ]
        );
        $log->increment('hit');
    }
}
