<?php

Route::domain('veri.zone')->group(function () { Route::get('/', function() { return redirect()->away('https://8vz.net'); }); });
Route::domain('www.veri.zone')->group(function () { Route::get('/', function() { return redirect()->away('https://8vz.net'); }); });
Route::domain('olive.veri.zone')->group(function () { Route::get('/', function() { return redirect()->away('https://8vz.net'); }); });

Route::get('/', 'HomeController@dashboard')->name('dashboard');

Route::get('uyari', 'HomeController@alert')->name('alert');

Route::get('manifest.json', 'HomeController@manifest')->name('olive.manifest');

Route::get('p/{r}', 'HomeController@ipLog');

Route::post('markdown/onizleme', 'MarkdownController@preview')->name('markdown.preview');
Route::post('veri-sayac', 'HomeController@dataCounter')->name('home.data.counter');

Route::post('kabul-et', 'HomeController@termVersion')->name('term.version');

Route::get('kaynaklar', 'HomeController@sources')->name('sources');

Route::prefix('kullanici')->group(function () {
    Route::get('{id}', 'UserController@profile')->name('user.profile');
});

Route::namespace('Forum')->prefix('forum')->group(function () {
    Route::get('/', 'ForumController@index')->name('forum.index');
    Route::post('kategoriler', 'ForumController@categoryJson')->name('forum.categories');

    Route::get('konu/{id?}', 'ForumController@threadForm')->name('forum.thread.form');
    Route::post('konu', 'ForumController@threadSave');
    Route::post('cevap', 'ForumController@replySave')->name('forum.reply.submit');

    Route::post('cevap/{id}', 'ForumController@replyGet')->name('forum.reply.get');
    Route::post('cevap/guncelle', 'ForumController@replyUpdate')->name('forum.reply.update');

    Route::post('durum', 'ForumController@threadStatus')->name('forum.thread.status');
    Route::post('sabit', 'ForumController@threadStatic')->name('forum.thread.static');
    Route::delete('sil', 'ForumController@messageDelete')->name('forum.message.delete');
    Route::post('en-iyi-cevap', 'ForumController@messageBestAnswer')->name('forum.message.best');
    Route::post('puan', 'ForumController@messageVote')->name('forum.message.vote');
    Route::post('spam', 'ForumController@messageSpam')->name('forum.message.spam');
    Route::post('tasi', 'ForumController@threadMove')->name('forum.thread.move');
    Route::post('takip', 'ForumController@threadFollow')->name('forum.thread.follow');

    Route::get('{group}:{section}/{id?}', 'ForumController@group')->name('forum.group')->where([ 'group' => '[a-zA-Z0-9-]+', 'section' => '[a-zA-Z0-9-]+' ]);
    Route::get('{slug}', 'ForumController@category')->name('forum.category');
    Route::get('{slug}/{fake_slug}-{id}', 'ForumController@thread')->name('forum.thread');
});

Route::prefix('raporlar')->group(function () {
    Route::get('/', 'ReportController@dashboard')->name('report.dashboard');
    Route::get('rapor/{id}/duzenle', 'ReportController@edit')->name('report.edit');
    Route::post('rapor/{id}/duzenle', 'ReportController@editSave');

    Route::get('rapor/{key}', 'ReportController@view')->name('report.view');
    Route::post('rapor/{key}', 'ReportController@view');
    Route::delete('rapor', 'ReportController@delete')->name('report.delete');

    Route::post('durum', 'ReportController@status')->name('report.status');
    Route::post('rapor-baslat', 'ReportController@start')->name('report.start');

    Route::post('sayfa/{id}', 'ReportController@page')->name('report.page');
    Route::patch('sayfa/{id}', 'ReportController@pageUpdate');
    Route::delete('sayfa', 'ReportController@pageDelete')->name('report.page.delete');
    Route::post('sayfa/sirala', 'ReportController@pageSort')->name('report.page.sort');
    Route::put('sayfa', 'ReportController@pageCreate')->name('report.page.create');

    Route::put('icerik', 'ReportController@dataCreate')->name('report.data.create');
    Route::patch('icerik/{id}', 'ReportController@dataUpdate')->name('report.data.update');

    Route::put('aggs', 'ReportController@aggsCreate')->name('report.aggs.create');
    Route::patch('aggs/{id}', 'ReportController@aggsUpdate')->name('report.aggs.update');
});

Route::prefix('crm')->group(function () {
    Route::get('/', 'CRMController@dashboard')->name('crm.dashboard');
});

Route::prefix('kalabaligin-dusuncesi')->group(function () {
    Route::get('/', 'BorsaController@main')->name('borsa.main');
    Route::post('veriler', 'BorsaController@data')->name('borsa.data');
    Route::post('grafik', 'BorsaController@graph')->name('borsa.graph');
});

Route::prefix('partner')->group(function () {
    Route::get('hesap-gecmisi', 'UserController@partnerHistory')->name('partner.history');
    Route::post('odeme-istegi', 'UserController@partnerPaymentRequest')->name('partner.payment.request');

    Route::prefix('kullanici-yonetimi')->group(function () {
        Route::get('/', 'UserController@partnerListView')->name('partner.user.list');

        Route::get('kullanici/{id?}', 'UserController@partnerUserView')->name('partner.user');
        Route::post('alt-partner-orani/{id?}', 'UserController@partnerSubPercent')->name('partner.user.percent');

        Route::post('kullanici/olustur', 'UserController@partnerUserCreate')->name('partner.user.create');
        Route::post('kullanici/guncelle', 'UserController@partnerUserUpdate')->name('partner.user.update');

        Route::post('json', 'UserController@partnerListJson')->name('partner.user.list.json');
    });
});

Route::get('test', 'TestController@test');
Route::post('test', 'TestController@test');

Route::post('organizasyon', 'HomeController@organisation')->name('dashboard.organisation');

Route::post('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::post('route-by-id', 'RouteController@generateById')->name('route.generate.id');

Route::post('panel-monitor', 'HomeController@monitor')->name('dashboard.monitor');
Route::post('intro/{key}', 'HomeController@intro')->name('intro')->where('key', '('.implode('|', config('system.intro.keys')).')');

Route::post('modul-ara', 'ModuleSearchController@search')->name('module.search');
Route::post('modul-git', 'ModuleSearchController@go')->name('module.go');

Route::prefix('organizasyon')->group(function () {
    Route::get('olustur', 'OrganisationController@offer')->name('organisation.create.offer');
    Route::post('olustur', 'OrganisationController@offerCreate');
    Route::patch('/', 'OrganisationController@update')->name('organisation.update');

    Route::patch('update/name', 'OrganisationController@updateName')->name('organisation.update.name');
});

Route::prefix('veri-kiyasla')->group(function () {
    Route::get('/', 'SearchController@compare')->name('compare.dashboard');
    Route::post('islem', 'SearchController@compareProcess')->name('compare.process');
});

Route::prefix('arama-motoru')->group(function () {
    Route::get('/', 'SearchController@dashboard')->name('search.dashboard');

    Route::post('kaydet', 'SearchController@save')->name('search.save');
    Route::delete('sil', 'SearchController@delete')->name('search.delete');
    Route::post('aramalar', 'SearchController@searches')->name('search.list');

    Route::post('/', 'SearchController@search');

    Route::post('analiz', 'AggregationController@search')->name('search.aggregation');
    Route::post('banner', 'AggregationController@banner')->name('search.banner');
});

Route::prefix('db')->group(function () {
    Route::get('{es_index}/{es_type}/{es_id}', 'ContentController@module')->name('content');
    Route::post('data/{es_index}/{es_type}/{es_id}', 'ContentController@data')->name('content.data');
    Route::post('siniflandir', 'ContentController@classifier');

    Route::post(
        'histogram/{type}/{period}/{es_id}/{es_index_key?}',
        'ContentController@histogram')->name('content.histogram')->where([
            'period' => '(daily|hourly)',
            'es_index_key' => '[a-z0-9-_\.]+'
        ]
    );

    $smilar_types = [
        'tweet_replies',
        'tweet_retweets',
        'tweet_quotes',
        'tweet_favorites',
        'tweet_deleted',
        'user_tweets',
        'user_replies',
        'user_quotes',
        'user_retweets',
        'user_favorites',
        'user_quotes_desc',
        'user_replies_desc',
        'user_retweets_desc',
        'user_deleted',
        'comment-video',
        'comment-channel'
    ];

    Route::post('benzer/{es_index}/{es_type}/{es_id}/{type?}', 'ContentController@smilar')->name('content.smilar')->where('type', '('.implode('|', $smilar_types).')');
    Route::post('aggregation/tweet/{type}/{id}', 'ContentController@tweetAggregation')->name('tweet.aggregation')->where('type', '(platforms|langs|mention_in|mention_out|hashtags|places|category)');
    Route::post('aggregation/article/{type}/{id}', 'ContentController@articleAggregation')->name('article.aggregation')->where('type', '(category)');
    Route::post('aggregation/document/{type}/{id}', 'ContentController@documentAggregation')->name('document.aggregation')->where('type', '(category)');
    Route::post('aggregation/media/{type}/{screen_name}', 'ContentController@mediaAggregation')->name('media.aggregation')->where('type', '(mention_out|mention_in|mention_out_public|hashtags|hashtags_public|places|category)');
    Route::post('aggregation/video/{type}/{id}', 'ContentController@videoAggregation')->name('video.aggregation')->where([ 'type' => '(titles|category)', 'id' => '[0-9a-zA-Z_-]+']);

    Route::post('video/yorum/{id}', 'ContentController@videoComments')->name('youtube.comments')->where('id', '[0-9a-zA-Z_-]+');
    Route::post('videos/{id}', 'ContentController@channelVideos')->name('youtube.videos')->where('id', '[0-9a-zA-Z_-]+');
});

Route::prefix('gercek-zamanli')->namespace('RealTime')->group(function () {
    Route::get('akis', 'RealTimeController@stream')->name('realtime.stream');
    Route::post('sorgu', 'RealTimeController@query')->name('realtime.query');
});

Route::prefix('trend')->namespace('Trend')->group(function () {
    Route::prefix('canli')->group(function () {
        Route::get('/', 'TrendController@live')->name('trend.live');
        Route::post('/', 'TrendController@liveRedis');
    });

    Route::prefix('arsiv')->group(function () {
        Route::get('/', 'TrendController@archive')->name('trend.archive');
        Route::post('/', 'TrendController@archiveData');
        Route::get('{id}', 'TrendController@archiveView')->name('trend.archive.view');
    });

    Route::prefix('populer-kaynaklar')->group(function () {
        Route::get('/', 'TrendController@popular')->name('trend.popular');
    });
});

Route::prefix('alarm')->group(function () {
    Route::get('/', 'AlarmController@dashboard')->name('alarm.dashboard');
    Route::post('liste', 'AlarmController@data')->name('alarm.data');

    Route::post('/', 'AlarmController@get')->name('alarm');
    Route::put('/', 'AlarmController@create');
    Route::patch('/', 'AlarmController@update');
    Route::delete('/', 'AlarmController@delete');
});

Route::prefix('arsivler')->group(function () {
    Route::get('/', 'ArchiveController@groups')->name('pin.groups');
    Route::post('/', 'ArchiveController@groupListJson');

    Route::post('islem', 'ArchiveController@groupGet')->name('pin.group');
    Route::put('islem', 'ArchiveController@groupCreate');
    Route::patch('islem', 'ArchiveController@groupUpdate');
    Route::delete('islem', 'ArchiveController@groupDelete');

    Route::get('{id}', 'ArchiveController@pins')->name('pin.pins');
    Route::post('yorum', 'ArchiveController@comment')->name('pin.comment');
    Route::post('pdf', 'ArchiveController@pdf')->name('pin.pdf');
    Route::post('{type}', 'ArchiveController@pin')->name('pin')->where('type', '(add|remove)');
    Route::get('url/{id}', 'ArchiveController@pinUrls')->name('pin.urls');
});

Route::prefix('ayarlar')->group(function () {
    Route::prefix('organizasyon')->group(function () {
        Route::get('/', 'OrganisationController@settings')->name('settings.organisation');

        Route::post('ayril', 'OrganisationController@leave')->name('settings.organisation.leave');
        Route::post('devret', 'OrganisationController@transfer')->name('settings.organisation.transfer');
        Route::delete('cikar', 'OrganisationController@remove')->name('settings.organisation.remove');
        Route::post('davet', 'OrganisationController@invite')->name('settings.organisation.invite');

        Route::prefix('fatura')->group(function () {
            Route::get('odeme', 'OrganisationController@payment')->name('organisation.invoice.payment');
            Route::get('odeme/{status}', 'OrganisationController@paymentStatus')->name('organisation.invoice.payment.status')->where('status', '(ok|fail)');
            Route::delete('iptal', 'OrganisationController@invoiceCancel')->name('settings.organisation.invoice.cancel');
            Route::get('{id}/{key?}', 'OrganisationController@invoice')->name('organisation.invoice');
        });
    });

    Route::prefix('destek')->group(function () {
        Route::get('{type?}', 'TicketController@list')->name('settings.support')->where('type', '('.implode('|', array_keys(config('system.ticket.types'))).')');
        Route::get('talep/{id}', 'TicketController@view')->name('settings.support.ticket');
        Route::patch('talep/{id}/kapat', 'TicketController@close')->name('settings.support.ticket.close');
        Route::put('talep/cevap', 'TicketController@reply')->name('settings.support.ticket.reply');
        Route::post('/', 'TicketController@submit')->name('settings.support.submit');
    });

    Route::get('hesap-bilgileri', 'UserController@account')->name('settings.account');
    Route::post('hesap-bilgileri', 'UserController@accountUpdate');

    Route::get('arama-gecmisi', 'UserController@searchHistory')->name('settings.search_history');
    Route::post('arama-gecmisi', 'UserController@searchHistoryData');
    Route::delete('arama-gecmisi', 'UserController@searchHistoryDelete');

    Route::get('mobil', 'UserController@mobile')->name('settings.mobile');
    Route::put('mobil/olustur', 'UserController@mobileCreate')->name('settings.mobile.create');
    Route::patch('mobil/dogrula', 'UserController@mobileVerification')->name('settings.mobile.verification');
    Route::delete('mobil/sil', 'UserController@mobileDelete')->name('settings.mobile.delete');
    Route::post('mobil/gonder', 'UserController@mobileResend')->name('settings.mobile.resend');

    Route::get('e-posta-bildirimleri', 'UserController@notifications')->name('settings.notifications');
    Route::patch('e-posta-bildirimleri', 'UserController@notificationUpdate')->name('settings.notification');

    Route::get('hesap-resmi', 'UserController@avatar')->name('settings.avatar');
    Route::post('hesap-resmi', 'UserController@avatarUpload');

    Route::get('api', 'UserController@account')->name('settings.api');
});

Route::prefix('geo')->group(function () {
    Route::post('countries', 'GeoController@countries')->name('geo.countries');
    Route::post('states', 'GeoController@states')->name('geo.states');
});

Route::prefix('veri-havuzu')->group(function () {
    Route::get('/', 'DataController@dashboard')->name('data_pool.dashboard');

    Route::prefix('twitter')->namespace('Twitter')->group(function () {
        Route::get('kelime-havuzu', 'DataController@keywordList')->name('twitter.keyword.list');
        Route::post('kelime-havuzu', 'DataController@keywordListJson');

        Route::put('kelime', 'DataController@keywordCreate')->name('twitter.keyword.create');
        Route::delete('kelime', 'DataController@keywordDelete')->name('twitter.keyword.delete');

        Route::get('kullanici-havuzu', 'DataController@accountList')->name('twitter.account.list');
        Route::post('kullanici-havuzu', 'DataController@accountListJson');

        Route::put('kullanici', 'DataController@accountCreate')->name('twitter.account.create');
        Route::delete('kullanici', 'DataController@accountDelete')->name('twitter.account.delete');
    });

    Route::prefix('youtube')->namespace('YouTube')->group(function () {
        Route::get('kelime-havuzu', 'DataController@keywordList')->name('youtube.keyword.list');
        Route::post('kelime-havuzu', 'DataController@keywordListJson');

        Route::put('kelime', 'DataController@keywordCreate')->name('youtube.keyword.create');
        Route::delete('kelime', 'DataController@keywordDelete')->name('youtube.keyword.delete');

        Route::get('kanal-havuzu', 'DataController@channelList')->name('youtube.channel.list');
        Route::post('kanal-havuzu', 'DataController@channelListJson');

        Route::put('kanal', 'DataController@channelCreate')->name('youtube.channel.create');
        Route::delete('kanal', 'DataController@channelDelete')->name('youtube.channel.delete');

        Route::get('video-havuzu', 'DataController@videoList')->name('youtube.video.list');
        Route::post('video-havuzu', 'DataController@videoListJson');

        Route::put('video', 'DataController@videoCreate')->name('youtube.video.create');
        Route::delete('video', 'DataController@videoDelete')->name('youtube.video.delete');
    });

    Route::prefix('instagram')->namespace('Instagram')->group(function () {
        Route::post('kullanici/senkronizasyon', 'DataController@userSync')->name('instagram.user.sync');

        Route::get('baglanti-havuzu', 'DataController@urlList')->name('instagram.url.list');
        Route::post('baglanti-havuzu', 'DataController@urlListJson');

        Route::put('baglanti', 'DataController@urlCreate')->name('instagram.url.create');
        Route::delete('baglanti', 'DataController@urlDelete')->name('instagram.url.delete');
    });
});

Route::prefix('oturum')->group(function () {
    Route::get('/', 'UserController@loginView')->name('user.login');
    Route::post('/', 'UserController@loginPost');
    Route::get('cikis', 'UserController@logout')->name('user.logout');

    Route::put('uyeol', 'UserController@registerPut')->name('user.register');
    Route::post('resend', 'UserController@registerResend')->name('user.register.resend');
    Route::get('dogrula/{user_id}/{sid}', 'UserController@registerValidate')->name('user.register.validate');
    Route::post('sifre', 'UserController@passwordGetPost')->name('user.password');
    Route::get('sifre/{user_id}/{sid}', 'UserController@passwordNew')->name('user.password.new');
    Route::patch('sifre/{user_id}/{sid}', 'UserController@passwordNewPatch');

    Route::get('{provider}/callback', 'CRMController@providerCallback')->name('provider.callback')->where('provider', '(twitter)');
    Route::get('{provider}/redirect', 'CRMController@providerRedirect')->name('provider.redirect')->where('provider', '(twitter)');
    Route::get('{provider}/{id}/drop', 'CRMController@providerDrop')->name('provider.drop')->where('provider', '(twitter)');
});

Route::get('yenilikler', 'HomeController@features')->name('home.features');

Route::post('demo-istek', 'HomeController@demoRequest')->name('demo.request');
Route::post('rapor-istek', 'HomeController@reportRequest')->name('report.request');

Route::prefix('gercek-zamanli')->namespace('RealTime')->group(function () {
    Route::post('sorgu/ornek', 'RealTimeController@querySample')->name('realtime.query.sample');
});

Route::get('{slug}', 'PageController@view')->name('page.view');
