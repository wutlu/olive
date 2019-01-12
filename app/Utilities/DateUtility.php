<?php

namespace App\Utilities;

use Carbon\Carbon;

class DateUtility
{
    # get date in dom
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

        $years = [
            date('Y', strtotime('- 1 year')),
            date('Y'),
            date('Y', strtotime('+ 1 year')),
        ];
        $months = [
            date('m', strtotime('- 1 month')),
            date('m'),
            date('m', strtotime('+ 1 month')),
        ];
        $days = [
            date('d', strtotime('- 1 day')),
            date('d', strtotime('- 2 day')),
            date('d', strtotime('- 3 day')),
            date('d', strtotime('- 4 day')),
            date('d', strtotime('- 5 day')),
            date('d', strtotime('- 6 day')),
            date('d', strtotime('- 7 day')),
            date('d'),
            date('d', strtotime('+ 1 day')),
        ];

        if ($format)
        {
            $formats = [
                $format
            ];
        }
        else
        {
            $formats = [
                '/((('.implode('|', $years).').('.implode('|', $months).').('.implode('|', $days).')).(([0-9]|1[0-9]|2[0-3]){2}\:([0-9]|[1-4][0-9]|5[0-9]){2}(\:([0-9]|[1-4][0-9]|5[0-9]){2})?((\+|\-)\d{2}\:\d{2})?)?)/',
                '/((('.implode('|', $days).').('.implode('|', $months).').('.implode('|', $years).')).(([0-9]|1[0-9]|2[0-3]){2}\:([0-9]|[1-4][0-9]|5[0-9]){2}(\:([0-9]|[1-4][0-9]|5[0-9]){2})?((\+|\-)\d{2}\:\d{2})?)?)/',
                //
            ];
        }

        foreach ($formats as $format)
        {
            preg_match_all(
                $format,
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
                        return self::checkDate($d[0]);
                    }
                }
            }
        }

        return false;
    }

    # check date
    public static function checkDate(string $date)
    {
        try
        {
            $date = new \DateTime($date);

            $full_date = $date->format('Y-m-d H:i:s');

            if (date('H:i:s') != '00:00:00' && $date->format('H:i:s') == '00:00:00')
            {
                $full_date = $date->format('Y-m-d').' '.date('H:i:s');
            }

            if ($full_date < date('Y-m-d H:i:s', strtotime('+ 10 minute')) && $full_date > date('Y-m-d H:i:s', strtotime('- 2 day')))
            {
                return $full_date;
            }
        }
        catch (\Exception $e)
        {
            //
        }

        return false;
    }
}
