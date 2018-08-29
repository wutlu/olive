<?php

namespace App\Utilities;

class DateUtility
{
	# is date
    public static function isDate($value)
    {
        if (!$value)
        {
            return false;
        }
        else
        {
            $value = mb_strtolower($value);

            $value = preg_replace(
                [
                    '/ ocak /',
                    '/ (ş|s)ubat /',
                    '/ mart /',
                    '/ nisan /',
                    '/ may(ı|i)s /',
                    '/ haziran /',
                    '/ temmuz /',
                    '/ a(ğ|g)ustos /',
                    '/ eyl(ü|u)l /',
                    '/ ekim /',
                    '/ kas(ı|i)m /',
                    '/ aral(ı|i)k /',

                    '/pazartesi/',
                    '/sal(ı|i)/',
                    '/(ç|c)ar(ş|s)amba/',
                    '/per(ş|s)embe/',
                    '/cuma/',
                    '/cumartesi/',
                    '/pazar/',

                    '/\s+/',
                    '/ - /'
                ],
                [
                    '-01-',
                    '-02-',
                    '-03-',
                    '-04-',
                    '-05-',
                    '-06-',
                    '-07-',
                    '-08-',
                    '-09-',
                    '-10-',
                    '-11-',
                    '-12-',

                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',

                    ' ',
                    ' '
                ],
                $value
            );
        }

        try
        {
            $date = new \DateTime($value);

            return $date->format('Y-m-d H:i:s');
        }
        catch (\Exception $e)
        {
            return false;
        }
    }
}
