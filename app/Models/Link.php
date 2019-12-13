<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'links';

    protected $fillable = [
        'key',
        'url'
    ];

    public static function generate(string $link)
    {
        $key = rand(100000, 999999);
        $status = 'process';

        while ($status == 'process')
        {
            $exists = self::where('key', $key)->exists();

            if ($exists)
            {
                $key = rand(100000, 999999);
            }
            else
            {
                $status = 'ok';

                self::create(
                    [
                        'key' => $key,
                        'url' => $link
                    ]
                );
            }
        }

        return route('link.get', $key);
    }
}
