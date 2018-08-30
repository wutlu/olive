<?php

use Illuminate\Database\Seeder;

use App\Models\Crawlers\MediaCrawler;

class MediaCrawlersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'name' => 'TWEETANALİZ',
                'site' => 'http://www.tweetanaliz.com',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{2,128})\/([a-zA-Z0-9-_]{6})?',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'HABERTÜRK',
                'site' => 'http://www.haberturk.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{2,128})-(\d{7,8})(-[a-z0-9-]{2,24})?',
                'selector_title' => 'h1',
                'selector_description' => '.news-detail-featured-description > h2'
            ],
            [
                'name' => 'YENİÇAĞ',
                'site' => 'http://www.yenicaggazetesi.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{6,9})h\.htm',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => '[itemprop="description"]'
            ],
            [
                'name' => 'TRTHABER',
                'site' => 'http://www.trthaber.com',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z-]{4,24})\/([a-z0-9-]{4,128})-(\d{6,9})\.html',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'BİRGÜN',
                'site' => 'https://www.birgun.net',
                'base' => '/',
                'url_pattern' => 'haber-detay\/([a-z0-9-]{4,128})-(\d{6,9})\.html',
                'selector_title' => 'article.haber-detay > .title',
                'selector_description' => 'article.haber-detay > .description'
            ],
            [
                'name' => 'AKŞAM',
                'site' => 'http://www.aksam.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})\/haber-(\d{6,9})',
                'selector_title' => '.newsdetailwrap > h1',
                'selector_description' => '.newsdetailwrap > h2'
            ],
            [
                'name' => 'KARAR',
                'site' => 'http://www.karar.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{6,9})',
                'selector_title' => 'h1.title',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'DHA',
                'site' => 'https://www.dha.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})\/haber-(\d{7,9})',
                'selector_title' => 'h1',
                'selector_description' => '.news-body > p.spot'
            ],
            [
                'name' => 'CUMHURİYET',
                'site' => 'http://www.cumhuriyet.com.tr',
                'base' => '/',
                'url_pattern' => '(?<=href="\/)\d{6,9}(?=")',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => '.news-short'
            ],
            [
                'name' => 'T24',
                'site' => 'http://t24.com.tr',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{4,128}),(\d{6,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => '.top-context > h2'
            ],
            [
                'name' => 'YENİAKİT',
                'site' => 'http://www.yeniakit.com.tr',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{4,128})-(\d{6,9})\.html',
                'selector_title' => 'header > h1',
                'selector_description' => 'header > h2'
            ],
            [
                'name' => 'MİLLİGAZETE',
                'site' => 'https://www.milligazete.com.tr',
                'base' => '/',
                'url_pattern' => 'haber\/(\d{7,9})\/([a-z0-9-]{4,128})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'GAZETEVATAN',
                'site' => 'http://www.gazetevatan.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{7,9})-([a-z0-9-]{4,24})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'GAZETECİLER',
                'site' => 'http://www.gazeteciler.com',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{4,128})\/(\d{6,9})',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'YURTGAZETESİ',
                'site' => 'http://www.yurtgazetesi.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-h(\d{5,9})\.html',
                'selector_title' => 'h3.title',
                'selector_description' => 'p.lead'
            ],
            [
                'name' => 'HÜRHABER',
                'site' => 'http://www.hurhaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{6,9})\.html',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => '.dsag > h2'
            ],
            [
                'name' => 'MEDYARADAR',
                'site' => 'http://www.medyaradar.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{16,128})-(\d{7,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'h2[itemprop="articleSection"]'
            ],
            [
                'name' => 'SOL',
                'site' => 'http://haber.sol.org.tr',
                'base' => 'anasayfa',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{6,9})',
                'selector_title' => 'h1.title > span',
                'selector_description' => '.singlenews-spot'
            ],
            [
                'name' => 'SÖZCÜ',
                'site' => 'https://www.sozcu.com.tr',
                'base' => '/',
                'url_pattern' => date('Y').'\/([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{7,9})',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'TÜRKİYEGAZETESİ',
                'site' => 'http://www.turkiyegazetesi.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/(\d{6,9})\.aspx',
                'selector_title' => 'h1',
                'selector_description' => 'h2.article_abstract'
            ],
            [
                'name' => 'SONDAKİKA',
                'site' => 'https://www.sondakika.com',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{4,128})-(\d{8,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'GAZETEYENİYÜZYIL',
                'site' => 'http://www.gazeteyeniyuzyil.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-h(\d{5,9})\.html',
                'selector_title' => 'h3.title',
                'selector_description' => 'p.lead'
            ],
            [
                'name' => 'YENİMESAJ',
                'site' => 'http://www.yenimesaj.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-h(\d{8,9})\.html',
                'selector_title' => 'h1.title',
                'selector_description' => 'p.lead'
            ],
            [
                'name' => 'OBJECKTİF HABER',
                'site' => 'http://www.objektifhaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{6,9})-haber',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'EVRENSEL',
                'site' => 'https://www.evrensel.net',
                'base' => '/',
                'url_pattern' => 'haber\/(\d{6,9})\/([a-z0-9-]{4,128})',
                'selector_title' => '.article-header > h1',
                'selector_description' => '.spot > h2'
            ],
            [
                'name' => 'DİKEN',
                'site' => 'http://www.diken.com.tr',
                'base' => '/',
                'url_pattern' => '(?<=\.com.tr\/)([a-z0-9-]{16,})(?=\/)',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => '.entry-content > p:first-child'
            ],
            [
                'name' => 'HABERVAKTİM',
                'site' => 'https://www.habervaktim.com',
                'base' => '/',
                'url_pattern' => 'haber\/(\d{6,9})\/([a-z0-9-]{4,128})\.html',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => '.short_content'
            ],
            [
                'name' => 'F5HABER',
                'site' => 'https://www.f5haber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{7,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'h2[itemprop="articleSection"]'
            ],
            [
                'name' => 'MEDYAFARESİ',
                'site' => 'http://www.medyafaresi.com',
                'base' => '/',
                'url_pattern' => 'haber\/([a-z0-9-]{4,128})\/(\d{6,9})',
                'selector_title' => '.news > h1',
                'selector_description' => '.news > h2'
            ],
            [
                'name' => 'AJANSHABER',
                'site' => 'http://www.ajanshaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})\/(\d{6,9})',
                'selector_title' => 'article > h1',
                'selector_description' => 'article > h2'
            ],
            [
                'name' => 'NTV',
                'site' => 'https://www.ntv.com.tr',
                'base' => '/',
                'url_pattern' => '(?<=href="\/)([a-z0-9-]{4,24})\/([a-z0-9-]{4,128}),([a-zA-Z0-9-_]{22})(?=")',
                'selector_title' => 'h1',
                'selector_description' => 'article > h2'
            ],
            [
                'name' => 'İHA',
                'site' => 'http://www.iha.com.tr',
                'base' => '/',
                'url_pattern' => 'haber-([a-z0-9-]{4,128})-(\d{6,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'TIMETURK',
                'site' => 'https://www.timeturk.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})\/haber-(\d{6,9})',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'ODATV',
                'site' => 'https://www.odatv.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-0(\d{7,9})\.html',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'CNNTÜRK',
                'site' => 'https://www.cnnturk.com',
                'base' => '/',
                'url_pattern' => '(?<=href="\/)([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})(?=")',
                'selector_title' => 'h1.detail-title',
                'selector_description' => 'h2.detail-description'
            ],
            [
                'name' => 'AHABER',
                'site' => 'https://www.ahaber.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/'.date('Y').'\/'.date('m').'\/'.date('d').'\/([a-z0-9-]{4,128})',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'MİLLİYET',
                'site' => 'http://www.milliyet.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{7,9})(?=\/)',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'TGRTHABER',
                'site' => 'http://www.tgrthaber.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{6,9})(?=")',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'h2.haber-spot'
            ],
            [
                'name' => 'İNTERNETHABER',
                'site' => 'http://www.internethaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{7,9})h\.htm',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'STAR',
                'site' => 'http://www.star.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-haber-(\d{7,9})',
                'selector_title' => 'h1',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'SABAH',
                'site' => 'https://www.sabah.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24}\/)?([a-z0-9-]{4,24})\/'.date('Y').'\/'.date('m').'\/'.date('d').'\/([a-z0-9-]{4,128})',
                'selector_title' => 'h1.pageTitle',
                'selector_description' => 'h2.spot'
            ],
            [
                'name' => 'HABER3',
                'site' => 'https://www.haber3.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24}\/)?([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{7,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'h2'
            ],
            [
                'name' => 'HABER7',
                'site' => 'http://www.haber7.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/haber\/(\d{7,9})-([a-z0-9-]{4,128})',
                'selector_title' => 'h1.title',
                'selector_description' => 'h2.spot'
            ],
            [
                'name' => 'YENİŞAFAK',
                'site' => 'https://www.yenisafak.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{7,9})',
                'selector_title' => 'h1.title',
                'selector_description' => 'h2.spot'
            ],
            [
                'name' => 'POSTA',
                'site' => 'http://www.posta.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{7,9})',
                'selector_title' => 'h1.news-detail__info__details',
                'selector_description' => 'h2.news-detail__info__spot'
            ],
            [
                'name' => 'HÜRRİYET',
                'site' => 'http://www.hurriyet.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{8,9})',
                'selector_title' => 'h1.news-detail-title',
                'selector_description' => '.news-detail-spot > h2'
            ],
            [
                'name' => 'NETGAZETE',
                'site' => 'http://www.netgazete.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{4,128})-(\d{6,9})(?=")',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p.haber-spot'
            ],
            [
                'name' => 'ENSONHABER',
                'site' => 'http://www.ensonhaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{10,128})\.html',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'HABERLER',
                'site' => 'https://www.haberler.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{8,9})-haberi',
                'selector_title' => 'h1#haber_baslik',
                'selector_description' => 'h2.spot'
            ],
            [
                'name' => 'MYNETHABER',
                'site' => 'http://www.mynet.com',
                'base' => 'haber',
                'url_pattern' => '([a-z0-9-]{4,128})-(\d{10,12})',
                'selector_title' => 'h1.detail-post-title',
                'selector_description' => 'h2.detail-post-spot'
            ],
            [
                'name' => 'MYNETSPOR',
                'site' => 'https://www.mynet.com',
                'base' => '/spor',
                'url_pattern' => '([a-z0-9-]{10,128})-(\d{6,9})-myspor',
                'selector_title' => 'h1.detail-post-title',
                'selector_description' => 'h2.detail-post-spot'
            ],
            [
                'name' => 'TAKVİM',
                'site' => 'https://www.takvim.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/'.date('Y').'\/'.date('m').'\/'.date('d').'\/([a-z0-9-]{10,128})',
                'selector_title' => 'h1#haberTitle',
                'selector_description' => 'h2#haberSpot'
            ],
            [
                'name' => 'WEBTEKNO',
                'site' => 'http://www.webtekno.com',
                'base' => 'haber',
                'url_pattern' => '([a-z0-9-]{10,128})-h(\d{5,9})\.html',
                'selector_title' => 'h1[itemprop="headline"] > a',
                'selector_description' => '[itemprop="description"]'
            ],
            [
                'name' => 'CHIP',
                'site' => 'https://www.chip.com.tr',
                'base' => 'haber',
                'url_pattern' => 'haber\/([a-z0-9-]{10,128})_(\d{5,9})\.html',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => 'p[itemprop="description"]'
            ],
            [
                'name' => 'BBC',
                'site' => 'https://www.bbc.com',
                'base' => 'turkce',
                'url_pattern' => 'turkce\/([a-z0-9-]{10,128})-(\d{8,9})',
                'selector_title' => 'h1.story-body__h1',
                'selector_description' => 'p.story-body__introduction'
            ],
            [
                'name' => 'ONEDİO',
                'site' => 'https://onedio.com',
                'base' => 'haberler',
                'url_pattern' => 'haber\/([a-z0-9-]{10,128})-(\d{6,9})',
                'selector_title' => 'h1',
                'selector_description' => '.text > h3'
            ],
            [
                'name' => 'DEMOKRATHABER',
                'site' => 'https://www.demokrathaber.org',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{10,128})-h(\d{6,9})\.html',
                'selector_title' => '.panel-title > h1',
                'selector_description' => '.panel-title > p'
            ],
            [
                'name' => 'SPUTNIKNEWS',
                'site' => 'https://tr.sputniknews.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9_-]{4,24})\/\d{4}\d{2}\d{4}(\d{10})-([a-z0-9-]{10,128})',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => '.b-article__lead > p'
            ],
            [
                'name' => 'BUSINESSHT',
                'site' => 'http://www.businessht.com.tr',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/haber\/(\d{7,9})-([a-z0-9-]{10,128})',
                'selector_title' => 'h1.subPageHeadLine',
                'selector_description' => 'h2.subPageSpot'
            ],
            [
                'name' => 'DÖVİZ',
                'site' => 'https://haber.doviz.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{10,128})\/(\d{6,9})',
                'selector_title' => 'h1[itemprop="headline"]',
                'selector_description' => '.left-column > p:first-child'
            ],
            [
                'name' => 'İLERİHABER',
                'site' => 'http://ilerihaber.org',
                'base' => '/',
                'url_pattern' => 'icerik\/([a-z0-9-]{10,128})-(\d{5,9})\.html',
                'selector_title' => '.waypoint > h1',
                'selector_description' => '.waypoint > h2.spot'
            ],
            [
                'name' => 'ETKİHABER',
                'site' => 'http://www.etkihaber.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{10,128})-(\d{6,9})h\.htm',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'MEPANEWS',
                'site' => 'https://www.mepanews.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{10,128})-(\d{5,9})h\.htm',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'KAMUAJANS',
                'site' => 'http://www.kamuajans.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{4,24})\/([a-z0-9-]{10,128})-h(\d{6,9})\.html',
                'selector_title' => 'h1[itemprop="name"]',
                'selector_description' => 'h2[itemprop="description"]'
            ],
            [
                'name' => 'SPORX',
                'site' => 'http://www.sporx.com',
                'base' => '/',
                'url_pattern' => '([a-z0-9-]{10,128})-([A-Z0-9-]){14}',
                'selector_title' => 'h1#habertitle',
                'selector_description' => 'h2#haberheadline'
            ],
        ];

        foreach ($items as $item)
        {
           	$query = MediaCrawler::updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'site' => $item['site'],
                    'base' => $item['base'],
                    'url_pattern' => $item['url_pattern'],
                    'selector_title' => $item['selector_title'],
                    'selector_description' => $item['selector_description'],
                ]
            );
        }
    }
}
