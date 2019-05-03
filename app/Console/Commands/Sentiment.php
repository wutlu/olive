<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Sentiment as SentimentLib;

use App\Utilities\Term;
use Sense;

class Sentiment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentiment:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duygu analizi testi.';

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
        $sentiment = new SentimentLib;
        $sentiment->engine('sentiment');

        $strings = [
            'çok güzel bir haber hemen not etmeliyim',
            'Gündemde bir konu gündeme çıkmasın istiyorlarsa kendi görevlilerini hemen devreye sokup ilk 10a bahis sitelerini yerleştiriyorlar. Hem sansür uygulamış hem de milleti twitterdan soğutmuş oluyorlar. bir taşla iki kuş mayıs',
            '🎁 500 TL İlk Yatırım Bonusu 🎁 💸 #Gobahise Üye Olunuz :https://t.co/V371mDoRXo 💸 #gobahisgiris #gobahistwitter #freebet #gobahisyeniadresi #gobahisbonus #canlıbahis #fenerinmacivar #iddaa #bonus #bahis #iddaa #casino #bonus #bahistahmini #euroleague #bilyoner https://t.co/ADp776CxYY',
            'Necati Şahin bir sonraki yerel seçimde hangi partiden nerenin Başkan adayı olacak ve kaybedecek? Bahisleri açıyorum',
            '🔘 İkinci Maçımız 🔘 ▫️ Swansea - Derby ▫️ Toplam Gol 2.5 Üst ▫️ Oran: 1.60 ▪️ Bahis 100-₺ ▪️ Olası Kazanç 160-₺ ♾ Desteklerinizi esirgemeyin! Bol şans! ✔️',
            '#NBAPlayoffs lar son hızıyla devam ediyor basketbolun kalbi #lunabet’de atıyor!! ⛹️ En yüksek oranlar ve zengin bahis seçenekleri ile bahsinizi #lunabet’ten alın, #basketbol heyecanını yakından yaşayın! 💪 ➡️https://t.co/11iXXExcoq⬅️ #nba #basketbol 🏀 #denver #portland https://t.co/G8ZvHBXsei',
            '💋0553 687 95 71 💋 🌹 anal oral bütün fanteziler var 💦duş imkanı vardır 🔴gerçek escort bayan arıyorsanız burdayım #çanakkalee??čôrt Çanakkale Escort Eskort Bayan escort ucuz escort gerçek escort Sex Öğrenci Liseli Sikiş Güzel Kız Türk Türbanlı Porno Fav porno Türk porno https://t.co/Kdx6nN53HH',
            'farin urlaub un ilk solo albümü olup, 22 ekim 2001de çıkmıştır. ayrıca "sonunda tatil!" anlamına gelen almanca sözdür. 1. intro (manche nennen es musik) - 1:03 2. jeden tag sonntag - 2:10 3. sumisu - 2:14 4. glücklich - 2:56 5. ich gehöre nicht dazu - 3:16 6. ok - 4:19 7. der kavalier - 3:27 8. am strand - 2:46 9. wunderbar - 2:39 10. das schöne mädchen - 4:37 11. 1000 jahre schlechten sex - 3:30 12. und die gitarre war noch warm - 3:39 13. lieber staat - 3:53 14. phänomenal egal - 3:13 15. abschiedslied - 3:28 16. outro (ja, das wurde auch zeit) - 2:22',
            'Sanal sex yapılır 30 dk 70 tl watsap veya skype görüntülü konuşma. Reel yok onun icin soranı boş muhabbet icin dm atanı ananında engelliyorum #sanalseks #sanalsex #sex #amcık #am #sevişme #boşalma #parmaklama #soyunma #sikiş #göt #meme #sik #show #skype #show #whatsapp #show',
            'BANU : 0539 381 43 64 EDA: 0534 939 35 44 👄SINIRSIZ İLİŞKİ İÇİN ARAYIN👄 #çorum??ſčòrț Çorum êſčòrț êſkòrț êsc bayan bayanlar grup üniversiteli porno liseli selfie dul kadın türbanlı masaj masöz grup seks sex öğrenci anal oral porn takipçi rt fav https://t.co/06ybRgjeLJ',
        ];

        foreach ($strings as $string)
        {
            $this->line($string);
            $this->info(json_encode($sentiment->score($string)));
        }
    }

    /**
     * Duygu Analizi veritabanı güncelle.
     *
     * @return mixed
     */
    public static function sentimentUpdate()
    {
        $sentiment = new SentimentLib;
        $sentiment->classes = [
            'pos', 'neg', 'neu', 'ign',
            'bet', 'nud', 'ign.illegal',
            'que', 'req',
            'gender.male', 'gender.female'
        ];
        $sentiment->update();

        echo Term::line('Sentiment belleği güncellendi.');
    }
}
