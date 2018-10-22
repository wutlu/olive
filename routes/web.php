<?php

Route::get('/', 'HomeController@index')->name('home');
Route::get('panel', 'HomeController@dashboard')->name('dashboard');
Route::get('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::get('route-by-id', 'RouteController@generateById')->name('route.generate.id');

Route::get('panel-monitor', 'HomeController@monitor')->name('dashboard.monitor');
Route::get('intro/{key}', 'HomeController@intro')->name('intro')->where('key', '('.implode('|', config('app.intro.keys')).')');

Route::prefix('organizasyon')->group(function () {
    Route::get('plan', 'OrganisationController@select')->name('organisation.create.select');
    Route::get('plan/{id}', 'OrganisationController@details')->name('organisation.create.details');
    Route::put('/', 'OrganisationController@create')->name('organisation.create');
    Route::patch('/', 'OrganisationController@update')->name('organisation.update');
    Route::get('/', 'OrganisationController@result')->name('organisation.create.result');

    Route::patch('update/name', 'OrganisationController@updateName')->name('organisation.update.name');
});

Route::get('uyari', 'HomeController@alert')->name('alert');

Route::prefix('ayarlar')->group(function () {
    Route::prefix('organizasyon')->group(function () {
        Route::get('/', 'OrganisationController@settings')->name('settings.organisation');

        Route::post('ayril', 'OrganisationController@leave')->name('settings.organisation.leave');
        Route::delete('sil', 'OrganisationController@delete')->name('settings.organisation.delete');
        Route::post('devret', 'OrganisationController@transfer')->name('settings.organisation.transfer');
        Route::delete('cikar', 'OrganisationController@remove')->name('settings.organisation.remove');
        Route::post('davet', 'OrganisationController@invite')->name('settings.organisation.invite');

        Route::delete('fatura-iptal', 'OrganisationController@invoiceCancel')->name('settings.organisation.invoice.cancel');
    });

    Route::prefix('destek')->group(function () {
        Route::get('{type?}', 'TicketController@list')->name('settings.support')->where('type', '('.implode('|', array_keys(config('app.ticket.types'))).')');
        Route::get('talep/{id}', 'TicketController@view')->name('settings.support.ticket');
        Route::patch('talep/{id}/kapat', 'TicketController@close')->name('settings.support.ticket.close');
        Route::put('talep/cevap', 'TicketController@reply')->name('settings.support.ticket.reply');
        Route::post('/', 'TicketController@submit')->name('settings.support.submit');
    });

    Route::get('fatura-gecmisi', 'OrganisationController@settings')->name('settings.invoices');

    Route::get('hesap-bilgileri', 'UserController@account')->name('settings.account');
    Route::post('hesap-bilgileri', 'UserController@accountUpdate');

    Route::get('e-posta-bildirimleri', 'UserController@notifications')->name('settings.notifications');
    Route::patch('e-posta-bildirimleri', 'UserController@notificationUpdate')->name('settings.notification');

    Route::get('hesap-resmi', 'UserController@avatar')->name('settings.avatar');
    Route::post('hesap-resmi', 'UserController@avatarUpload');

    Route::get('api', 'UserController@account')->name('settings.api');
});

Route::prefix('fatura')->group(function () {
    Route::get('{id}', 'OrganisationController@invoice')->name('organisation.invoice');
    Route::post('hesapla', 'OrganisationController@calculate')->name('organisation.create.calculate');
    Route::post('hesapla-uzat', 'OrganisationController@calculateRenew')->name('organisation.create.calculate.renew');
});

Route::prefix('geo')->group(function () {
    Route::get('countries', 'GeoController@countries')->name('geo.countries');
    Route::get('states', 'GeoController@states')->name('geo.states');
});

Route::prefix('twitter')->namespace('Twitter')->group(function () {
    Route::prefix('oauth')->group(function () {
        Route::get('/', 'AccountController@connect')->name('twitter.connect');
        Route::post('redirect', 'AccountController@redirect')->name('twitter.connect.redirect');
        Route::post('disconnect', 'AccountController@disconnect')->name('twitter.disconnect');

        Route::get('callback', 'AccountController@callback')->name('twitter.connect.callback');
    });

    Route::get('veri-havuzu', 'DataController@dataPool')->name('twitter.data.pool');
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
});

# #### [ ADMIN ] #### #
Route::prefix('admin')->middleware([ 'root' ])->group(function () {
    Route::patch('ayar', 'System\SystemController@set')->name('admin.set');

    Route::prefix('sistem-izleme')->group(function () {
        Route::get('sunucu-bilgisi', 'MonitorController@server')->name('admin.monitoring.server');
        Route::post('sunucu-bilgisi', 'MonitorController@serverJson');

        Route::get('log-ekrani', 'MonitorController@log')->name('admin.monitoring.log');
        Route::post('log-ekrani', 'MonitorController@logJson');
        Route::delete('log-ekrani/temizle', 'MonitorController@logClear')->name('admin.monitoring.log.clear');

        Route::get('kuyruk-ekrani', 'MonitorController@queue')->name('admin.monitoring.queue');

        Route::get('arkaplan', 'MonitorController@background')->name('admin.monitoring.background');
        Route::post('arkaplan', 'MonitorController@backgroundProcess');
    });

    Route::prefix('kupon-yonetimi')->group(function () {
        Route::get('/', 'DiscountController@adminCouponListView')->name('admin.discount.coupon.list');
        Route::get('kupon/{id?}', 'DiscountController@adminCouponView')->name('admin.discount.coupon');
        Route::put('kupon', 'DiscountController@adminCouponCreate');
        Route::patch('kupon', 'DiscountController@adminCouponUpdate');
        Route::delete('kupon', 'DiscountController@adminCouponDelete');

        Route::get('indirim-gunleri', 'DiscountController@adminDayListView')->name('admin.discount.day.list');
        Route::get('indirim-gunu/{id?}', 'DiscountController@adminDayView')->name('admin.discount.day');
        Route::put('indirim-gunu', 'DiscountController@adminDayCreate');
        Route::patch('indirim-gunu', 'DiscountController@adminDayUpdate');
        Route::delete('indirim-gunu', 'DiscountController@adminDayDelete');
    });

    Route::prefix('bot-yonetimi')->namespace('Crawlers')->group(function () {
        Route::prefix('alisveris-botlari')->group(function () {
            Route::get('/', 'ShoppingController@listView')->name('crawlers.shopping.list');
            Route::get('json', 'ShoppingController@listViewJson')->name('crawlers.shopping.list.json');

            Route::get('bot/{id?}', 'ShoppingController@view')->name('crawlers.shopping.bot');
            Route::get('bot/{id}/istatistik', 'ShoppingController@statistics')->name('crawlers.shopping.bot.statistics');
            Route::post('bot/durum', 'ShoppingController@status')->name('crawlers.shopping.bot.status');
            Route::patch('bot', 'ShoppingController@update');
            Route::delete('bot', 'ShoppingController@delete');

            Route::get('genel/istatistik', 'ShoppingController@allStatistics')->name('crawlers.shopping.bot.statistics.all');
            Route::post('genel/baslat', 'ShoppingController@allStart')->name('crawlers.shopping.bot.start.all');
            Route::post('genel/durdur', 'ShoppingController@allStop')->name('crawlers.shopping.bot.stop.all');
            Route::post('genel/index-olustur', 'ShoppingController@allIndex')->name('crawlers.shopping.bot.index.all');
        });

        Route::prefix('youtube')->group(function () {
            Route::get('/', 'YouTubeController@dashboard')->name('admin.youtube.settings');
            Route::patch('ayar', 'YouTubeController@statusSet')->name('admin.youtube.status.set');

            Route::post('log-ekrani', 'YouTubeController@logJson')->name('admin.youtube.monitoring.log');

            Route::get('index-durumu', 'YouTubeController@indexStatus')->name('admin.youtube.index.status');
            Route::post('index-olustur', 'YouTubeController@indexCreate')->name('admin.youtube.index.create');
        });

        Route::prefix('google')->group(function () {
            Route::get('/', 'GoogleController@dashboard')->name('admin.google.settings');
            Route::patch('ayar', 'GoogleController@statusSet')->name('admin.google.status.set');

            Route::post('log-ekrani', 'GoogleController@logJson')->name('admin.google.monitoring.log');

            Route::get('index-durumu', 'GoogleController@indexStatus')->name('admin.google.index.status');
            Route::post('index-olustur', 'GoogleController@indexCreate')->name('admin.google.index.create');
        });

        Route::prefix('twitter')->group(function () {
            Route::get('/', 'TwitterController@dashboard')->name('admin.twitter.settings');
            Route::get('istatistik', 'TwitterController@statistics')->name('admin.twitter.statistics');
            Route::get('index-yonetimi', 'TwitterController@indices')->name('admin.twitter.indices');
            Route::get('index-yonetimi/json', 'TwitterController@indicesJson')->name('admin.twitter.indices.json');

            Route::post('index-olustur', 'TwitterController@indexCreate')->name('admin.twitter.index.create');
            Route::get('index-durumu', 'TwitterController@indexStatus')->name('admin.twitter.index.status');

            Route::patch('ayar', 'TwitterController@set')->name('admin.twitter.option.set');

            Route::post('log-ekrani', 'TwitterController@logJson')->name('admin.twitter.monitoring.log');
        });

        Route::prefix('medya-botlari')->group(function () {
            Route::get('/', 'MediaController@listView')->name('crawlers.media.list');
            Route::get('json', 'MediaController@listViewJson')->name('crawlers.media.list.json');

            Route::get('bot/{id?}', 'MediaController@view')->name('crawlers.media.bot');
            Route::get('bot/{id}/istatistik', 'MediaController@statistics')->name('crawlers.media.bot.statistics');
            Route::post('bot/durum', 'MediaController@status')->name('crawlers.media.bot.status');
            Route::patch('bot', 'MediaController@update');
            Route::delete('bot', 'MediaController@delete');

            Route::get('genel/istatistik', 'MediaController@allStatistics')->name('crawlers.media.bot.statistics.all');
            Route::post('genel/baslat', 'MediaController@allStart')->name('crawlers.media.bot.start.all');
            Route::post('genel/durdur', 'MediaController@allStop')->name('crawlers.media.bot.stop.all');
            Route::post('genel/index-olustur', 'MediaController@allIndex')->name('crawlers.media.bot.index.all');
        });

        Route::prefix('sozluk-botlari')->group(function () {
            Route::get('/', 'SozlukController@listView')->name('crawlers.sozluk.list');
            Route::get('json', 'SozlukController@listViewJson')->name('crawlers.sozluk.list.json');

            Route::get('bot/{id?}', 'SozlukController@view')->name('crawlers.sozluk.bot');
            Route::get('bot/{id}/istatistik', 'SozlukController@statistics')->name('crawlers.sozluk.bot.statistics');
            Route::post('bot/durum', 'SozlukController@status')->name('crawlers.sozluk.bot.status');
            Route::patch('bot', 'SozlukController@update');
            Route::delete('bot', 'SozlukController@delete');

            Route::get('genel/istatistik', 'SozlukController@allStatistics')->name('crawlers.sozluk.bot.statistics.all');
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
        Route::get('json', 'UserController@adminListViewJson')->name('admin.user.list.json');

        Route::get('kullanici/{id}', 'UserController@adminView')->name('admin.user');
        Route::post('kullanici/{id}', 'UserController@adminUpdate');

        Route::get('kullanici/{id}/bildirimler', 'UserController@adminNotifications')->name('admin.user.notifications');
        Route::patch('kullanici/{id}/bildirim', 'UserController@adminNotificationUpdate')->name('admin.user.notification');

        Route::get('kullanici/{id}/fatura-gecmisi', 'UserController@adminInvoiceHistory')->name('admin.user.invoices');
        Route::get('kullanici/{id}/destek-talepleri', 'UserController@adminTickets')->name('admin.user.tickets');
    });

    Route::prefix('organizasyon-yonetimi')->group(function () {
        Route::get('/', 'OrganisationController@adminListView')->name('admin.organisation.list');
        Route::get('json', 'OrganisationController@adminListViewJson')->name('admin.organisation.list.json');

        Route::get('organizasyon/{id}', 'OrganisationController@adminView')->name('admin.organisation');
        Route::post('organizasyon/{id}', 'OrganisationController@adminUpdate');

        Route::get('organizasyon/{id}/fatura-gecmisi', 'OrganisationController@adminInvoiceHistory')->name('admin.organisation.invoices');
        Route::post('organizasyon/{id}/fatura-onay', 'OrganisationController@adminInvoiceApprove')->name('admin.organisation.invoice.approve');
    });
});

Route::get('{slug}', 'PageController@view')->name('page.view');
