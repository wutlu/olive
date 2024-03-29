<?php

namespace App\Utilities;

use File;
use Parsedown;

use LanguageDetection\Language;

class Term
{
    /**
     * Benzer Kelime Tespiti
     *
     * @return array
     */
    public static function commonWords(string $string, int $max_count = 4)
    {
        $string = self::convertAscii($string, [ 'lowercase' => true ]);

        $stop_words = explode(PHP_EOL, File::get(database_path('analysis/stopwords.txt')));

        preg_match_all('/\b([a-zğüşıöç]{4,})\b/i', $string, $match_words);

        $match_words = $match_words[0];

        foreach ($match_words as $key => $item)
        {
            if ($item == '' || in_array(strtolower($item), $stop_words) || strlen($item) <= 3)
            {
                unset($match_words[$key]);
            }
        }  

        $word_count = str_word_count(implode(' ', $match_words), 1, 'ğüşıöç');

        $frequency = array_count_values($word_count);

        arsort($frequency);

        $keywords = array_slice($frequency, 0, $max_count);

        return $keywords;
    }

    /**
     * Cli Satır
     *
     * @return string
     */
    public static function line(string $string)
    {
        $arr[] = '['.date('H:i:s').']';
        $arr[] = $string;
        $arr[] = PHP_EOL;

        return implode(' ', $arr);
    }

    /**
     * Hit Sırala
     *
     * @return array
     */
    public static function hitSort($a, $b)
    {
        return $b['hit'] - $a['hit'];
    }

    /**
     * Dil Tespiti
     *
     * @return boolean
     */
    public static function languageDetector(array $bucket, string $lang = 'tr')
    {
        foreach ($bucket as $line)
        {
            if ($line)
            {
                $detect = (new Language)->detect($line)->bestResults()->__toString();

                if ($detect == $lang)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Markdown
     *
     * @return string
     */
    public static function markdown($text)
    {
        $parsedown = new Parsedown;
        $parsedown->setSafeMode(true);
        $parsedown->setBreaksEnabled(true);
        $parsedown->setUrlsLinked(true);

        return $parsedown->text($text);
    }

    /**
     * Ascii karakter düzeltici.
     *
     * @return string
     */
    public static function convertAscii($str, $options = [])
    {
        $str = mb_convert_encoding((string) $str, 'UTF-8', mb_list_encodings());

        $defaults = [
            'delimiter' => ' ',
            'limit' => null,
            'lowercase' => false,
            'uppercase' => false,
            'replacements' => [],
            'transliterate' => false
        ];

        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
            'ß' => 'ss', 
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
            'Ž' => 'Z', 
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z', 
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
            'Ż' => 'Z', 
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z',
        );

        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        if ($options['transliterate'])
        {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        $str = preg_replace('/[^\p{L}\p{Nd}\.\/\'\"\,\$\(\)\:\;\#\-\?\!\=\n\%]+/u', $options['delimiter'], $str);
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        $str = trim($str, $options['delimiter']);

        if ($options['lowercase'])
        {
            $str = mb_strtolower($str, 'UTF-8');
        }

        if ($options['uppercase'])
        {
            $str = mb_strtoupper($str, 'UTF-8');
        }

        return $str;
    }

    /**
     * Okunaklı dosya boyutları.
     *
     * @return object
     */
    public static function humanFileSize(int $bytes, int $decimals = 2)
    {
        $sizes = [
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        ];

        $factor = floor((strlen($bytes) - 1) / 3);
        $readable = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor));

        return (object) [
            'size' => $bytes,
            'readable' => $readable.' '.@$sizes[$factor]
        ];
    }

    /**
     * Temiz bir arama sorgusu oluşturur.
     *
     * @return object
     */
    public static function cleanSearchQuery($text = '')
    {
        $clean = str_replace([ '*' ], '', $text);
        $clean = preg_replace('/@([A-Za-z0-9_\/\.]*)/', 'user.screen_name:$1', $clean);
        $clean = preg_replace('/((.+):( |$)|(?!(.+)))/m', '', $clean);
        $clean = trim($clean);
        $clean = ltrim($clean, '&&||');
        $clean = rtrim($clean, '&&||');

        if (substr_count($clean, '"')%2)
        {
            $clean = str_replace('"', ' ', $clean);
        }

        $words_raw = str_replace([ ' OR ', ' AND ', ')', '(', '"', '\'', '-', '+', '^', '~', '#', '||', '&&' ], ' ', $text);
        $words_raw = explode(' ', $words_raw);

        return (object) [
            'line' => $clean,
            'words' => array_values(array_filter($words_raw))
        ];
    }

    /**
     * Auto Link
     *
     * - Twitter Tweet gövdelerindeki hashtag, mention ve linkleri html formatında çıktı verir.
     *
     * @return string
     */
    public static function tweet(string $tweet)
    {
        //Convert urls to <a> links
        $tweet = preg_replace('/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/', '<a target="_blank" href="$1">$1</a>', $tweet);

        //Convert hashtags to twitter searches in <a> links
        $tweet = preg_replace('/#([A-ZğüşıöçĞÜŞİÖÇa-z0-9\/\.]*)/', '<a target="_blank" href="'.route('search.dashboard').'?q=$1">#$1</a>', $tweet);

        //Convert attags to twitter profiles in &lt;a&gt; links
        $tweet = preg_replace('/@([A-Za-z0-9_\/\.]*)/', '<a target="_blank" href="'.route('search.dashboard').'?q=@$1">@$1</a>', $tweet);

        return mb_convert_encoding($tweet, 'UTF-8', 'UTF-8');
    }

    /**
     * Auto Link
     *
     * - Twitter Tweet gövdelerindeki hashtag, mention ve linkleri html formatında çıktı verir.
     *
     * @return string
     */
    public static function instagramMedia(string $media)
    {
        //Convert urls to <a> links
        $media = preg_replace('/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/', '<a target="_blank" href="$1">$1</a>', $media);

        //Convert hashtags to twitter searches in <a> links
        $media = preg_replace('/#([A-ZğüşıöçĞÜŞİÖÇa-z0-9\/\.]*)/', '<a target="_blank" href="'.route('search.dashboard').'?q=$1">#$1</a>', $media);

        return mb_convert_encoding($media, 'UTF-8', 'UTF-8');
    }

    /**
     * Auto Link
     *
     * - Genel kullanım için linklerin Olive için çevrilmesini sağlar.
     *
     * @return string
     */
    public static function linked(string $string)
    {
        //Convert urls to <a> links
        $string = preg_replace('/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/', '<a target="_blank" href="$1">$1</a>', $string);

        //Convert hashtags to twitter searches in <a> links
        $string = preg_replace('/#([A-ZğüşıöçĞÜŞİÖÇa-z0-9\/\.]*)/', '<a target="_blank" href="'.route('search.dashboard').'?q=$1">#$1</a>', $string);

        $string = nl2br($string);

        return $string;
    }
}
