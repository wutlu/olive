<?php

namespace App\Olive;

class Analysis
{
    private $adult;
    private $bet;

    /**
     * Load Keywords
     *
     * @return string
     */
    public function loadKeywords()
    {
        $this->adult = [
            'êſčòrț',
            'escort',
            'türbanlı',
            'éſčòrț',
            'çıtır',
            'ifşa',
            'ensest',
            'porn',
            'sikiş',
            'sex',
            '??ſčòrț',
            'seks',
            'eskort',
            'evli',
            'çift',
            'karısını',
            'evli',
            'gizli',
            'swinger',
            'gay',
            'ens',
            'cuckold',
            'godoş',
            'gavat',
            'boynuzlu',
            'ibne',
            'aktif',
            'pasif',
            'tutkun',
            'azgın',
            'olgun',
            'dolgun',
            'erotik',
            'lezbiyen',
            'fantezi',
            'otel',
            'bayan',
            'anal',
            'oral',
            'duş',
            'esc',
            'yatak',
            'dul',
        ];
        $this->bet = [
            'bet',
            'bahis',
            'bets',
            'rulet',
            'bonus',
        ];
        $this->hate = [
            'bunlar',
            'vereceksiniz',
            'sorulacak',
            'istifa',
            'hain',
            'nefret',
            'istemiyoruz',
            'terorist',
            'fasist',
            'irkci',
            'tecavuzcu',
            'narsist',
            'sadist',
            'şeytan',
            'cehennem',
            'belasini',
            'belanizi',
            'serefsiz',
            'adi',
            'pislik',
            'diktator',
            'lanet',
            'orospu',
            'belasini',
        ];
        $this->question = [
            'kimim',
            'kimin',
            'kimler',
            'kimlerin',
            'kimse',
            'kim',
            'neden',
            'nasil',
            'yardim',
            'eder',
            'misin',
            'misiniz',
            'miyim',
            'mis',
            'mu',
            'mus',
            'muyum',
            'musun',
            'muyuz',
            'mi',
            'miyim',
            'misin',
            'miyiz',
            'muyuz',
            'musunuz',
            'ki',
            'ne',
            'oldun',
            'oldunuz',
            'neler',
            'olabilir',
        ];
    }

    /**
     * Adult Detector
     *
     * @return string
     */
    public function detector(string $text)
    {
        $rating = [
            'adult' => 0,
            'bet' => 0,
            'hate' => 0,
            'question' => 0,
        ];

        foreach ($rating as $key => $rate)
        {
            foreach ($this->{$key} as $word)
            {
                if (str_contains(strtolower($text), $word))
                {
                    $rating[$key] = $rating[$key]+1;
                }
            }

            if ($key == 'question' && strpos($text, '?'))
            {
                $rating['question'] = $rating['question']+1;
            }
        }

        return $rating;
    }
}
