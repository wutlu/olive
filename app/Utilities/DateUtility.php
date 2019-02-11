<?php

namespace App\Utilities;

use Carbon\Carbon;

use Term;

class DateUtility
{
    /**
     * Metin içerisinden tarih tespiti.
     *
     * @return array
     */
    public static function getDateInDom(string $dom, string $format = '')
    {
        $dom = Term::convertAscii($dom, [ 'lowercase' => true ]);

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
                '/cumartesi/',
                '/cuma/',
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

            intval(date('m', strtotime('- 1 month'))),
            intval(date('m')),
            intval(date('m', strtotime('+ 1 month'))),
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

            intval(date('d', strtotime('- 1 day'))),
            intval(date('d', strtotime('- 2 day'))),
            intval(date('d', strtotime('- 3 day'))),
            intval(date('d', strtotime('- 4 day'))),
            intval(date('d', strtotime('- 5 day'))),
            intval(date('d', strtotime('- 6 day'))),
            intval(date('d', strtotime('- 7 day'))),
            intval(date('d')),
            intval(date('d', strtotime('+ 1 day'))),
        ];

        if ($format)
        {
            $formats = [
                $format
            ];
        }
        else
        {
            $date_sep = '(-|\.|\/| )';
            $formats = [
                '/((('.implode('|', $years).')'.$date_sep.'('.implode('|', $months).')'.$date_sep.'('.implode('|', $days).')).(([0-9]|1[0-9]|2[0-3]){2}\:([0-9]|[1-4][0-9]|5[0-9]){2}(\:([0-9]|[1-4][0-9]|5[0-9]){2})?))/',
                '/((('.implode('|', $days).')'.$date_sep.'('.implode('|', $months).')'.$date_sep.'('.implode('|', $years).')).(([0-9]|1[0-9]|2[0-3]){2}\:([0-9]|[1-4][0-9]|5[0-9]){2}(\:([0-9]|[1-4][0-9]|5[0-9]){2})?))/',
                '/((('.implode('|', $years).')'.$date_sep.'('.implode('|', $months).')'.$date_sep.'('.implode('|', $days).')))/',
                '/((('.implode('|', $days).')'.$date_sep.'('.implode('|', $months).')'.$date_sep.'('.implode('|', $years).')))/',
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
                        $date = self::checkDate($d[0]);

                        if ($date)
                        {
                            return $date;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Tarih Doğrula
     *
     * @return array
     */
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

            if ($full_date < date('Y-m-d H:i:s', strtotime('+ 10 minute')) && $full_date > date('Y-m-d H:i:s', strtotime('- 7 day')))
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

    /**
     * Tarih Sırala
     *
     * @return array
     */
    public static function dateSort($a, $b)
    {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    }
}
