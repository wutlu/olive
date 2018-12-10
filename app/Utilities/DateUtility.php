<?php

namespace App\Utilities;

use Carbon\Carbon;
use DateTime;

class DateUtility
{
	# is date
    public static function getDateInDom(string $dom, string $format = '')
    {
        $dom = mb_strtolower($dom);
        $dom = preg_replace(
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
            $dom
        );

        $year = (object) [
            'before' => date('Y', strtotime('- 1 year')),
            'now' => date('Y'),
            'after' => date('Y', strtotime('+ 1 year'))
        ];
        $month = (object) [
            'before' => date('m', strtotime('- 1 month')),
            'now' => date('m'),
            'after' => date('m', strtotime('+ 1 month'))
        ];
        $day = (object) [
            'before' => date('d', strtotime('- 1 day')),
            'now' => date('d'),
            'after' => date('d', strtotime('+ 1 day'))
        ];

        preg_match_all(
            $format ? $format : '/((('.$year->before.'|'.$year->now.'|'.$year->after.').('.$month->before.'|'.$month->now.'|'.$month->after.').('.$day->before.'|'.$day->now.'|'.$day->after.')|('.$day->before.'|'.$day->now.'|'.$day->after.').('.$month->before.'|'.$month->now.'|'.$month->after.').('.$year->before.'|'.$year->now.'|'.$year->after.')).(([0-9]|1[0-9]|2[0-3]){2}\:([0-9]|[1-4][0-9]|5[0-9]){2}(\:([0-9]|[1-4][0-9]|5[0-9]){2})?((\+|\-)\d{2}\:\d{2})?)?)/',
            $dom,
            $dates,
            PREG_SET_ORDER
        );

        if (count($dates))
        {
            foreach ($dates as $d)
            {
                if (@$d[0])
                {
                    try
                    {
                        $date = new DateTime($d[0]);

                        $full_date = $date->format('Y-m-d H:i:s');

                        if (date('H:i:s') != '00:00:00' && $date->format('H:i:s') == '00:00:00')
                        {
                            $full_date = $date->format('Y-m-d').' '.date('H:i:s');
                        }

                        if ($full_date < date('Y-m-d H:i:s', strtotime('+ 10 minute')) && $full_date > date('Y-m-d H:i:s', strtotime('- 12 hour')))
                        {
                            return $full_date;
                        }
                    }
                    catch (\Exception $e)
                    {
                        return false;
                    }
                }
            }

            return false;
        }
        else
        {
            return false;
        }
    }
}
