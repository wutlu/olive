<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use System;

use Term;

use App\Olive\Gender;

use Sentiment;
use Sense;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test komutu.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sentiment = new Sense;

        $items = [
            'RT @DemiralpUguz: 69209 https://t.co/8Opx83LCc5 #izmir??ſčòrț SEKSİ SİNEM Sabah 6\'da yataktan fırlayan, giyinip zorla bir şeyler atıştır…',
            'RT @DulFatma39: Kökleme böyle olur https://t.co/9N4gxVNG12',
            'RT @Tulay03414281: Karsiyaka SEÇIL #Karsiyaka🍒💖 💘✔💛 #izmir??ſčòrț💚❤ 💗 🔞🔞🔞#bornova??ſčòrț 🔞🔞 🔞🔞 💋💖 💜💗 💖💗💗 💜💚🍒 🍒🍒 🍒🍒 #karsiyakaeskort 💞 🌡🌡🌡…',
            'İyi akşamlar Türkiye 🇹🇷 Good evening Earth 🌏 https://t.co/7cYfi3v6lv',
            'Bu ürün hakkında daha çok bilgiye sahip olabilir miyiz?',
            'Bu araç ne yakıyor',
            'Fiyatı ne',
            'Ne lanet',
        ];

        foreach ($items as $text)
        {
            $g = $sentiment->score($text);

            $this->line($text);
            $this->info(json_encode($g));
        }
    }
}
