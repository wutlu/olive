<?php

namespace App\Utilities;

use Carbon\Carbon;
use DateTime;

class DateUtility
{
    /*
     * tarih deseni
     * ---------------[ DİKKAT ]------------------------------
     * BU DESEN 2028'E KADAR GEÇERLİDİR.
     * EN GEÇ 2027 ARALIK AYINDA GÜNCELLENMESİ GEREKİR.
     * -------------------------------------------------------
     */
    public static function datePattern()
    {
        return '/((201[89]|202[0-8])|\d{1,2})(\.|-| )(\d{1,2}|([a-zA-ZŞşıİğĞüÜ]{4,8}))(\.|-| )(\d{4}|\d{2})(( |, )[a-zA-ZÇçŞşığĞüÜ]{4,10})?((.| - )(\d{1,2}):(\d{1,2})(:(\d{1,2}))?((.?(\d{1,2}):(\d{1,2}))|Z)?)?/';
    }

	# is date
    public static function isDate($value)
    {
        if ($value)
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
        else
        {
            return false;
        }

        try
        {
            $date = new DateTime($value);

            $full_date = $date->format('Y-m-d H:i:s');

            if (date('H:i:s') != '00:00:00' && $date->format('H:i:s') == '00:00:00')
            {
                $full_date = $date->format('Y-m-d').' '.date('H:i:s');
            }

            if ($full_date > date('Y-m-d H:i:s', strtotime('+ 10 minute')) && $full_date < date('Y-m-d H:i:s', strtotime('- 2 day')))
            {
                return false;
            }

            return $full_date;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }
}
