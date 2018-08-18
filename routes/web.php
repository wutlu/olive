<?php

Route::get('/', 'HomeController@index')->name('home');
Route::get('panel', 'HomeController@dashboard')->name('dashboard');
Route::get('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::get('route-by-id', 'RouteController@generateById')->name('route.generate.id');

Route::get('panel-monitor', 'HomeController@monitor')->name('dashboard.monitor');
Route::get('intro/{key}', 'HomeController@intro')->name('intro')->where('key', '('.implode('|', config('app.intro.keys')).')');;

Route::prefix('organizasyon')->group(function () {
    Route::get('plan', 'OrganisationController@select')->name('organisation.create.select');
    Route::get('plan/{id}', 'OrganisationController@details')->name('organisation.create.details');
    Route::put('/', 'OrganisationController@create')->name('organisation.create');
    Route::patch('/', 'OrganisationController@update')->name('organisation.update');
    Route::get('/', 'OrganisationController@result')->name('organisation.create.result');

    Route::patch('update/name', 'OrganisationController@updateName')->name('organisation.update.name');
});

# #### [ ADMIN ] #### #
Route::prefix('admin')->middleware([ 'root' ])->group(function () {
    Route::prefix('kupon-yonetimi')->group(function () {
        Route::get('kuponlar', 'DiscountController@adminCouponListView')->name('admin.discount.coupon.list');
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

    Route::prefix('destek-talepleri')->group(function () {
        Route::get('{status?}', 'TicketController@adminList')->name('admin.tickets')->where('status', '(open|closed)');

        Route::get('talep/{id}', 'TicketController@adminView')->name('admin.ticket');
        Route::patch('talep/{id}/kapat', 'TicketController@adminClose')->name('admin.ticket.close');
        Route::put('talep/cevap', 'TicketController@adminReply')->name('admin.ticket.reply');
    });

    Route::prefix('kullanici-yonetimi')->group(function () {
        Route::get('kullanicilar', 'UserController@adminListView')->name('admin.user.list');
        Route::get('kullanicilar/json', 'UserController@adminListViewJson')->name('admin.user.list.json');

        Route::get('kullanici/{id}', 'UserController@adminView')->name('admin.user');
        Route::post('kullanici/{id}', 'UserController@adminUpdate');

        Route::get('kullanici/{id}/bildirimler', 'UserController@adminNotifications')->name('admin.user.notifications');
        Route::patch('kullanici/{id}/bildirim', 'UserController@adminNotificationUpdate')->name('admin.user.notification');

        Route::get('kullanici/{id}/fatura-gecmisi', 'UserController@adminInvoiceHistory')->name('admin.user.invoices');
        Route::get('kullanici/{id}/destek-talepleri', 'UserController@adminTickets')->name('admin.user.tickets');
    });

    Route::prefix('organizasyon-yonetimi')->group(function () {
        Route::get('organizasyonlar', 'OrganisationController@adminListView')->name('admin.organisation.list');
        Route::get('organizasyonlar/json', 'OrganisationController@adminListViewJson')->name('admin.organisation.list.json');

        Route::get('organizasyon/{id}', 'OrganisationController@adminView')->name('admin.organisation');
        Route::post('organizasyon/{id}', 'OrganisationController@adminUpdate');

        Route::get('organizasyon/{id}/fatura-gecmisi', 'OrganisationController@adminInvoiceHistory')->name('admin.organisation.invoices');
        Route::post('organizasyon/{id}/fatura-onay', 'OrganisationController@adminInvoiceApprove')->name('admin.organisation.invoice.approve');
    });
});

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
        Route::get('talepler/{type?}', 'TicketController@list')->name('settings.support')->where('type', '('.implode('|', array_keys(config('app.ticket.types'))).')');
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

Route::prefix('kullanici')->group(function () {
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
