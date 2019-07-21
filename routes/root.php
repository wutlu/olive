<?php

Route::prefix('sistem-izleme')->group(function () {
    Route::get('sunucu-bilgisi', 'MonitorController@server')->name('admin.monitoring.server');
    Route::post('sunucu-bilgisi', 'MonitorController@serverJson');

    Route::get('log-ekrani', 'MonitorController@log')->name('admin.monitoring.log');
    Route::post('log-ekrani', 'MonitorController@logJson');
    Route::delete('log-ekrani/temizle', 'MonitorController@logClear')->name('admin.monitoring.log.clear');

    Route::prefix('arkaplan')->group(function () {
        Route::get('/', 'MonitorController@background')->name('admin.monitoring.background');
        Route::post('islemler', 'MonitorController@backgroundProcesses')->name('admin.monitoring.background.processes');
        Route::post('sonlandir', 'MonitorController@processKill')->name('admin.monitoring.process.kill');
    });

    Route::prefix('ziyaretci-loglari')->group(function () {
        Route::get('/', 'SessionController@logs')->name('admin.session.logs');
        Route::post('/', 'SessionController@logsJson');

        Route::post('aktiviteler', 'MonitorController@activity')->name('admin.session.activities');
    });
});

Route::post('elasticsearch/index/durum', 'DataController@elasticsearchIndexStatus')->name('elasticsearch.index.status');

Route::prefix('vekil-sunucu-yonetimi')->group(function () {
    Route::get('/', 'ProxyController@proxies')->name('admin.proxies');
    Route::post('json', 'ProxyController@proxiesJson')->name('admin.proxies.json');
    Route::post('vekil-sunucu', 'ProxyController@proxy')->name('admin.proxy');
    Route::put('vekil-sunucu', 'ProxyController@proxyCreate');
    Route::patch('vekil-sunucu', 'ProxyController@proxyUpdate');
    Route::delete('vekil-sunucu', 'ProxyController@proxyDelete');
});

Route::prefix('hosts-dosyasi')->group(function () {
    Route::get('/', 'HostsController@hostsFile')->name('admin.hosts.file');
});

Route::prefix('trend')->namespace('Trend')->group(function () {
    Route::get('/', 'RootController@dashboard')->name('admin.trend.settings');
    Route::patch('ayar', 'RootController@statusSet')->name('admin.trend.status.set');

    Route::post('log-ekrani', 'RootController@logJson')->name('admin.trend.monitoring.log');

    Route::post('index-durumu', 'RootController@indexStatus')->name('admin.trend.index.status');
    Route::post('index-olustur', 'RootController@indexCreate')->name('admin.trend.index.create');
});

Route::prefix('bot-yonetimi')->namespace('Crawlers')->group(function () {
    Route::get('/', function () { return view('crawlers.dashboard'); })->name('crawlers');

    # 
    # ALIŞVERİŞ
    # 
    Route::prefix('alisveris-botlari')->group(function () {
        Route::get('/', 'ShoppingController@listView')->name('crawlers.shopping.list');
        Route::post('json', 'ShoppingController@listViewJson')->name('crawlers.shopping.list.json');

        Route::get('bot/{id?}', 'ShoppingController@view')->name('crawlers.shopping.bot');
        Route::post('bot/{id}/istatistik', 'ShoppingController@statistics')->name('crawlers.shopping.bot.statistics');
        Route::post('bot/{id}/temizle', 'ShoppingController@clear')->name('crawlers.shopping.bot.clear');
        Route::post('bot/durum', 'ShoppingController@status')->name('crawlers.shopping.bot.status');
        Route::patch('bot', 'ShoppingController@update');
        Route::delete('bot', 'ShoppingController@delete');

        Route::post('genel/istatistik', 'ShoppingController@allStatistics')->name('crawlers.shopping.bot.statistics.all');
        Route::post('genel/baslat', 'ShoppingController@allStart')->name('crawlers.shopping.bot.start.all');
        Route::post('genel/durdur', 'ShoppingController@allStop')->name('crawlers.shopping.bot.stop.all');
        Route::post('genel/index-olustur', 'ShoppingController@allIndex')->name('crawlers.shopping.bot.index.all');
        Route::post('genel/temizle', 'ShoppingController@allClear')->name('crawlers.shopping.bot.clear.all');
    });

    # 
    # YOUTUBE
    # 
    Route::prefix('youtube')->namespace('YouTube')->group(function () {
        Route::get('/', 'YouTubeController@dashboard')->name('admin.youtube.settings');

        Route::post('istatistik', 'YouTubeController@statistics')->name('admin.youtube.statistics');
        Route::post('log-ekrani', 'YouTubeController@logJson')->name('admin.youtube.monitoring.log');

        Route::post('index-durumu', 'YouTubeController@indexStatus')->name('admin.youtube.index.status');
        Route::post('index-olustur', 'YouTubeController@indexCreate')->name('admin.youtube.index.create');

        Route::patch('ayar', 'YouTubeController@set')->name('admin.youtube.option.set');

        Route::get('index-yonetimi', 'YouTubeController@indices')->name('admin.youtube.indices');
        Route::post('index-yonetimi/json', 'YouTubeController@indicesJson')->name('admin.youtube.indices.json');

        Route::prefix('veri-havuzu')->group(function () {
            Route::get('kelime-havuzu/{id?}', 'DataController@keywordList')->name('admin.youtube.followed_keywords');
            Route::post('kelime-havuzu', 'DataController@keywordListJson');
            Route::patch('kelime-havuzu', 'DataController@keywordReason')->name('admin.youtube.followed_keywords.reason');

            Route::get('kanal-havuzu/{id?}', 'DataController@channelList')->name('admin.youtube.followed_channels');
            Route::post('kanal-havuzu', 'DataController@channelListJson');
            Route::patch('kanal-havuzu', 'DataController@channelReason')->name('admin.youtube.followed_channels.reason');

            Route::get('video-havuzu/{id?}', 'DataController@videoList')->name('admin.youtube.followed_videos');
            Route::post('video-havuzu', 'DataController@videoListJson');
            Route::patch('video-havuzu', 'DataController@videoReason')->name('admin.youtube.followed_videos.reason');
        });
    });

    # 
    # TWITTER
    # 
    Route::prefix('twitter')->namespace('Twitter')->group(function () {
        Route::get('/', 'TwitterController@dashboard')->name('admin.twitter.settings');

        Route::post('istatistik', 'TwitterController@statistics')->name('admin.twitter.statistics');
        Route::post('log-ekrani', 'TwitterController@logJson')->name('admin.twitter.monitoring.log');

        Route::post('index-durumu', 'TwitterController@indexStatus')->name('admin.twitter.index.status');
        Route::post('index-olustur', 'TwitterController@indexCreate')->name('admin.twitter.index.create');

        Route::patch('ayar', 'TwitterController@set')->name('admin.twitter.option.set');

        Route::get('index-yonetimi', 'TwitterController@indices')->name('admin.twitter.indices');
        Route::post('index-yonetimi/json', 'TwitterController@indicesJson')->name('admin.twitter.indices.json');

        Route::prefix('token-yonetimi')->group(function () {
            Route::post('json', 'TokenController@tokensJson')->name('admin.twitter.tokens.json');
            Route::post('token', 'TokenController@token')->name('admin.twitter.token');
            Route::put('token', 'TokenController@tokenCreate');
            Route::patch('token', 'TokenController@tokenUpdate');
            Route::delete('token', 'TokenController@tokenDelete');
        });

        Route::prefix('veri-havuzu')->group(function () {
            Route::get('kelime-havuzu/{id?}', 'DataController@keywordList')->name('admin.twitter.stream.keywords');
            Route::post('kelime-havuzu', 'DataController@keywordListJson');
            Route::patch('kelime-havuzu', 'DataController@keywordReason')->name('admin.twitter.stream.keywords.reason');

            Route::get('kullanici-havuzu/{id?}', 'DataController@accountList')->name('admin.twitter.stream.accounts');
            Route::post('kullanici-havuzu', 'DataController@accountListJson');
            Route::patch('kullanici-havuzu', 'DataController@accountReason')->name('admin.twitter.stream.accounts.reason');

            Route::prefix('trend/engelli-kelimeler')->group(function () {
                Route::get('/{id?}', 'DataController@blockedTrendKeywordList')->name('admin.twitter.trend.blocked_keywords');
                Route::post('/', 'DataController@blockedTrendKeywordListJson');
                Route::delete('/', 'DataController@blockedTrendKeywordDelete');
                Route::put('/', 'DataController@blockedTrendKeywordCreate');
            });
        });
    });

    # 
    # INSTAGRAM
    # 
    Route::prefix('instagram')->namespace('Instagram')->group(function () {
        Route::get('/', 'InstagramController@dashboard')->name('admin.instagram.settings');

        Route::post('istatistik', 'InstagramController@statistics')->name('admin.instagram.statistics');
        Route::post('log-ekrani', 'InstagramController@logJson')->name('admin.instagram.monitoring.log');

        Route::post('index-durumu', 'InstagramController@indexStatus')->name('admin.instagram.index.status');
        Route::post('index-olustur', 'InstagramController@indexCreate')->name('admin.instagram.index.create');

        Route::patch('ayar', 'InstagramController@set')->name('admin.instagram.option.set');

        Route::get('index-yonetimi', 'InstagramController@indices')->name('admin.instagram.indices');
        Route::post('index-yonetimi/json', 'InstagramController@indicesJson')->name('admin.instagram.indices.json');

        Route::prefix('veri-havuzu')->group(function () {
            Route::get('baglanti-havuzu/{id?}', 'DataController@urlList')->name('admin.instagram.urls');
            Route::post('baglanti-havuzu', 'DataController@urlListJson');
            Route::patch('baglanti-havuzu', 'DataController@urlReason')->name('admin.instagram.urls.reason');

            Route::prefix('trend/engelli-kelimeler')->group(function () {
                Route::get('/{id?}', 'DataController@blockedTrendKeywordList')->name('admin.instagram.trend.blocked_keywords');
                Route::post('/', 'DataController@blockedTrendKeywordListJson');
                Route::delete('/', 'DataController@blockedTrendKeywordDelete');
                Route::put('/', 'DataController@blockedTrendKeywordCreate');
            });
        });
    });

    # 
    # MEDYA
    # 
    Route::prefix('medya-botlari')->group(function () {
        Route::get('/', 'MediaController@listView')->name('crawlers.media.list');
        Route::post('json', 'MediaController@listViewJson')->name('crawlers.media.list.json');

        Route::get('bot/{id?}', 'MediaController@view')->name('crawlers.media.bot');
        Route::post('bot/{id}/istatistik', 'MediaController@statistics')->name('crawlers.media.bot.statistics');
        Route::post('bot/{id}/temizle', 'MediaController@clear')->name('crawlers.media.bot.clear');
        Route::post('bot/durum', 'MediaController@status')->name('crawlers.media.bot.status');
        Route::patch('bot', 'MediaController@update');
        Route::delete('bot', 'MediaController@delete');

        Route::post('genel/istatistik', 'MediaController@allStatistics')->name('crawlers.media.bot.statistics.all');
        Route::post('genel/baslat', 'MediaController@allStart')->name('crawlers.media.bot.start.all');
        Route::post('genel/durdur', 'MediaController@allStop')->name('crawlers.media.bot.stop.all');
        Route::post('genel/temizle', 'MediaController@allClear')->name('crawlers.media.bot.clear.all');

        Route::get('index-yonetimi', 'MediaController@indices')->name('crawlers.media.indices');
        Route::post('index-yonetimi/json', 'MediaController@indicesJson')->name('crawlers.media.indices.json');
        Route::post('index-olustur', 'MediaController@indexCreate')->name('crawlers.media.index.create');
    });

    # 
    # BLOG
    # 
    Route::prefix('blog-botlari')->group(function () {
        Route::get('/', 'BlogController@listView')->name('crawlers.blog.list');
        Route::post('json', 'BlogController@listViewJson')->name('crawlers.blog.list.json');

        Route::get('bot/{id?}', 'BlogController@view')->name('crawlers.blog.bot');
        Route::post('bot/{id}/istatistik', 'BlogController@statistics')->name('crawlers.blog.bot.statistics');
        Route::post('bot/{id}/temizle', 'BlogController@clear')->name('crawlers.blog.bot.clear');
        Route::post('bot/durum', 'BlogController@status')->name('crawlers.blog.bot.status');
        Route::patch('bot', 'BlogController@update');
        Route::delete('bot', 'BlogController@delete');

        Route::post('genel/istatistik', 'BlogController@allStatistics')->name('crawlers.blog.bot.statistics.all');
        Route::post('genel/baslat', 'BlogController@allStart')->name('crawlers.blog.bot.start.all');
        Route::post('genel/durdur', 'BlogController@allStop')->name('crawlers.blog.bot.stop.all');
        Route::post('genel/temizle', 'BlogController@allClear')->name('crawlers.blog.bot.clear.all');

        Route::get('index-yonetimi', 'BlogController@indices')->name('crawlers.blog.indices');
        Route::post('index-yonetimi/json', 'BlogController@indicesJson')->name('crawlers.blog.indices.json');
        Route::post('index-olustur', 'BlogController@indexCreate')->name('crawlers.blog.index.create');
    });

    # 
    # SÖZLÜK
    # 
    Route::prefix('sozluk-botlari')->group(function () {
        Route::get('/', 'SozlukController@listView')->name('crawlers.sozluk.list');
        Route::post('json', 'SozlukController@listViewJson')->name('crawlers.sozluk.list.json');

        Route::get('bot/{id?}', 'SozlukController@view')->name('crawlers.sozluk.bot');
        Route::post('bot/{id}/istatistik', 'SozlukController@statistics')->name('crawlers.sozluk.bot.statistics');
        Route::post('bot/durum', 'SozlukController@status')->name('crawlers.sozluk.bot.status');
        Route::patch('bot', 'SozlukController@update');
        Route::delete('bot', 'SozlukController@delete');

        Route::post('genel/istatistik', 'SozlukController@allStatistics')->name('crawlers.sozluk.bot.statistics.all');
        Route::post('genel/baslat', 'SozlukController@allStart')->name('crawlers.sozluk.bot.start.all');
        Route::post('genel/durdur', 'SozlukController@allStop')->name('crawlers.sozluk.bot.stop.all');
        Route::post('genel/index-olustur', 'SozlukController@allIndex')->name('crawlers.sozluk.bot.index.all');
    });
});
