<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;

    protected $table = 'emails';

    protected $fillable = [
        'email'
    ];

    public static function detector(string $string)
    {
        $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';

        preg_match_all($regexp, $string, $m);

        $emails = [];

        if (@$m[0])
        {
            foreach ($m[0] as $email)
            {
                $explode = explode('.', $email);

                if (!array_key_exists(end($explode), [ 'png' => true, 'jpg' => true, 'jpeg' => true, 'gif' => true ]))
                {
                    $emails[] = $email;

                    $entities = [ 'email' => $email ];

                    $item = self::where($entities)->withTrashed()->exists();

                    if (!$item)
                    {
                        try
                        {
                            self::insert(array_merge($entities, [ 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s') ]));
                        }
                        catch (\Exception $e)
                        {
                            //
                        }
                    }
                }
            }
        }

        return @$emails;
    }
}
