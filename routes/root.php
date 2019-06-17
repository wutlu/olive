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

Route::prefix('carousel-yonetimi')->group(function () {
    Route::get('/', 'CarouselController@carousels')->name('admin.carousels');
    Route::post('json', 'CarouselController@carouselsJson')->name('admin.carousels.json');
    Route::post('carousel', 'CarouselController@carousel')->name('admin.carousel');
    Route::put('carousel', 'CarouselController@carouselCreate');
    Route::patch('carousel', 'CarouselController@carouselUpdate');
    Route::delete('carousel', 'CarouselController@carouselDelete');
    Route::post('siralama', 'CarouselController@sortable')->name('admin.carousel.sortable');
});

Route::prefix('forum-yonetimi')->namespace('Forum')->group(function () {
    Route::prefix('kategori')->group(function () {
        Route::post('/', 'ForumController@categoryGet')->name('admin.forum.category');
        Route::put('/', 'ForumController@categoryCreate');
        Route::patch('/', 'ForumController@categoryUpdate');
        Route::delete('/', 'ForumController@categoryDelete');
    });
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

Route::prefix('sayfa-yonetimi')->group(function () {
    Route::get('/', 'PageController@adminListView')->name('admin.page.list');
    Route::get('sayfa/{id?}', 'PageController@adminView')->name('admin.page');
    Route::put('sayfa', 'PageController@adminCreate');
    Route::patch('sayfa', 'PageController@adminUpdate');
    Route::delete('sayfa', 'PageController@adminDelete');
});

Route::prefix('destek-talepleri')->group(function () {
    Route::get('{status?}', 'TicketController@adminList')->name('admin.tickets')->where('status', '(open|closed)');

    Route::get('talep/{id}', 'TicketController@adminView')->name('admin.ticket');
    Route::patch('talep/{id}/kapat', 'TicketController@adminClose')->name('admin.ticket.close');
    Route::put('talep/cevap', 'TicketController@adminReply')->name('admin.ticket.reply');
});

Route::prefix('kullanici-yonetimi')->group(function () {
    Route::get('/', 'UserController@adminListView')->name('admin.user.list');
    Route::post('json', 'UserController@adminListViewJson')->name('admin.user.list.json');
    Route::post('json/autocomplete', 'UserController@adminAutocomplete')->name('admin.user.autocomplete');

    Route::get('kullanici/{id}', 'UserController@adminView')->name('admin.user');
    Route::post('kullanici', 'UserController@adminCreate')->name('admin.user.register');
    Route::post('kullanici/{id}', 'UserController@adminUpdate');

    Route::get('kullanici/{id}/bildirimler', 'UserController@adminNotifications')->name('admin.user.notifications');
    Route::patch('kullanici/{id}/bildirim', 'UserController@adminNotificationUpdate')->name('admin.user.notification');

    Route::get('kullanici/{id}/fatura-gecmisi', 'UserController@adminInvoiceHistory')->name('admin.user.invoices');
    Route::get('kullanici/{id}/destek-talepleri', 'UserController@adminTickets')->name('admin.user.tickets');
});

Route::prefix('bulten')->group(function () {
    Route::get('/', 'NewsletterController@dashboard')->name('admin.newsletter');
    Route::post('/', 'NewsletterController@json');
    Route::get('islem/{id?}', 'NewsletterController@form')->name('admin.newsletter.form');
    Route::post('kayit', 'NewsletterController@save')->name('admin.newsletter.form.save');
    Route::post('durum', 'NewsletterController@status')->name('admin.newsletter.status');
    Route::delete('/', 'NewsletterController@delete')->name('admin.newsletter.delete');

    Route::post('kullanici-listesi', 'NewsletterController@users')->name('admin.newsletter.users');
});

Route::prefix('organizasyon-yonetimi')->group(function () {
    Route::get('/', 'OrganisationController@adminListView')->name('admin.organisation.list');
    Route::post('json', 'OrganisationController@adminListViewJson')->name('admin.organisation.list.json');

    Route::get('fiyat-ayarlari', 'OrganisationController@adminPriceSettings')->name('admin.organisation.price.settings');
    Route::post('fiyat-ayarlari', 'OrganisationController@adminPriceSettingsSave');

    Route::prefix('organizasyon')->group(function () {
        Route::get('{id}', 'OrganisationController@adminView')->name('admin.organisation');
        Route::post('{id}', 'OrganisationController@adminUpdate');
        Route::post('/', 'OrganisationController@adminCreate')->name('admin.organisation.create');

        Route::get('{id}/gercek-zamanli/kelime-gruplari', 'OrganisationController@keywordGroups')->name('admin.organisation.keyword_groups');
        Route::post('gercek-zamanli/kelime-gruplari/guncelle', 'OrganisationController@keywordGroupsUpdate')->name('admin.organisation.keyword_groups.update');

        Route::get('{id}/alarmlar', 'OrganisationController@alarms')->name('admin.organisation.alarms');
        Route::post('{id}/alarmlar', 'OrganisationController@alarmListJson');

        Route::get('{id}/pin-gruplari', 'OrganisationController@pinGroups')->name('admin.organisation.pin_groups');
        Route::post('{id}/pin-gruplari', 'OrganisationController@pinGroupListJson');

        Route::get('{id}/fatura-gecmisi', 'OrganisationController@adminInvoiceHistory')->name('admin.organisation.invoices');
        Route::post('{id}/fatura-onay', 'OrganisationController@adminInvoiceApprove')->name('admin.organisation.invoice.approve');
    });
});

Route::delete('sil/{es_index}/{es_type}/{es_id}', 'ContentController@delete')->name('admin.content.delete');
