<?php

Route::get('/', 'HomeController@index')->name('home');
Route::get('test', 'TestController@test');
Route::get('panel', 'HomeController@dashboard')->name('dashboard');
Route::post('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::post('route-by-id', 'RouteController@generateById')->name('route.generate.id');

Route::post('panel-monitor', 'HomeController@monitor')->name('dashboard.monitor');
Route::post('intro/{key}', 'HomeController@intro')->name('intro')->where('key', '('.implode('|', config('app.intro.keys')).')');

Route::post('modul-ara', 'ModuleSearchController@search')->name('module.search');
Route::post('modul-git', 'ModuleSearchController@go')->name('module.go');

Route::prefix('organizasyon')->group(function () {
    Route::get('plan', 'OrganisationController@select')->name('organisation.create.select');
    Route::get('plan/{id}', 'OrganisationController@details')->name('organisation.create.details');
    Route::put('/', 'OrganisationController@create')->name('organisation.create');
    Route::patch('/', 'OrganisationController@update')->name('organisation.update');
    Route::get('/', 'OrganisationController@result')->name('organisation.create.result');

    Route::patch('update/name', 'OrganisationController@updateName')->name('organisation.update.name');
});

Route::get('uyari', 'HomeController@alert')->name('alert');

Route::prefix('icerik')->group(function () {
    Route::get('{index}/{type}/{id}', 'RealTimeController@dashboard')->name('elasticsearch.document');
});

Route::prefix('gercek-zamanli')->namespace('RealTime')->group(function () {
    Route::get('akis', 'RealTimeController@stream')->name('realtime.stream');

    Route::post('sorgu', 'RealTimeController@query')->name('realtime.query');

    Route::prefix('kelime')->group(function () {
        Route::prefix('gruplar')->group(function () {
            Route::post('/', 'KeywordController@groups')->name('realtime.keyword.groups');

            Route::post('grup', 'KeywordController@groupGet')->name('realtime.keyword.group');
            Route::put('grup', 'KeywordController@groupCreate');
            Route::patch('grup', 'KeywordController@groupUpdate');
            Route::delete('grup', 'KeywordController@groupDelete');
        });
    });
});

Route::prefix('trend-analizi')->group(function () {
    Route::get('/', 'TrendController@dashboard')->name('trend.live');
    Route::get('arsiv', 'TrendController@archive')->name('trend.archive');
    Route::get('endeks', 'TrendController@index')->name('trend.index');
});

Route::prefix('pinleme')->group(function () {
    Route::prefix('gruplar')->group(function () {
        Route::get('/', 'PinController@groups')->name('pin.groups');
        Route::post('/', 'PinController@groupListJson');

        Route::post('grup', 'PinController@groupGet')->name('pin.group');
        Route::put('grup', 'PinController@groupCreate');
        Route::patch('grup', 'PinController@groupUpdate');
        Route::delete('grup', 'PinController@groupDelete');
    });

    Route::get('{id}', 'PinController@pins')->name('pin.pins');
    Route::post('yorum', 'PinController@comment')->name('pin.comment');
    Route::post('pdf', 'PinController@pdf')->name('pin.pdf');
    Route::post('{type}', 'PinController@pin')->name('pin')->where('type', '(add|remove)');
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
    Route::get('{id}/{key?}', 'OrganisationController@invoice')->name('organisation.invoice');
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
});

Route::prefix('veri-havuzu')->group(function () {
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

Route::get('{slug}', 'PageController@view')->name('page.view');
