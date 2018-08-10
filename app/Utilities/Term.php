<?php

namespace App\Utilities;

use File;
use Parsedown;

class Term
{
    # common words
    public static function commonWords(string $string, int $max_count = 5)
    {
        $string = preg_replace('/ss+/i', '', $string);
        $string = trim($string);
        $string = preg_replace('/[^a-zA-Z -]/', '', $string);
        $string = strtolower($string);

        $stop_words = explode(PHP_EOL, File::get(database_path('words/stop.txt')));

        preg_match_all('/\b.*?\b/i', $string, $match_words);

        $match_words = $match_words[0];

        foreach ($match_words as $key => $item)
        {
            if ($item == '' || in_array(strtolower($item), $stop_words) || strlen($item) <= 3)
            {
                unset($match_words[$key]);
            }
        }  

        $word_count = str_word_count( implode(" ", $match_words) , 1); 
        $frequency = array_count_values($word_count);

        arsort($frequency);

        $keywords = array_slice($frequency, 0, $max_count);

        return $keywords;
    }

    # cli line
    public static function line(string $string)
    {
        $arr[] = '['.date('H:i:s').']';
        $arr[] = $string;
        $arr[] = PHP_EOL;

        return implode(' ', $arr);
    }

    # markdown
    public static function markdown(string $text)
    {
        $parsedown = new Parsedown;

        return $parsedown->text($text);
    }
}
