<?php

Route::get('/', 'HomeController@index')->name('home');
Route::get('panel', 'HomeController@dashboard')->name('dashboard');
Route::get('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::prefix('organizasyon')->group(function () {
    Route::get('plan', 'OrganisationController@select')->name('organisation.create.select');
    Route::get('plan/{id}', 'OrganisationController@details')->name('organisation.create.details');
    Route::post('/', 'OrganisationController@create')->name('organisation.create');
    Route::get('/', 'OrganisationController@result')->name('organisation.create.result');
});

Route::prefix('ayarlar')->group(function () {
    Route::get('/', 'SettingsController@index')->name('settings');
    Route::get('organizasyon', 'OrganisationController@settings')->name('settings.organisation');
});

Route::prefix('fatura')->group(function () {
    Route::get('{id}', 'OrganisationController@invoice')->name('organisation.invoice');
    Route::post('hesapla', 'OrganisationController@calculate')->name('organisation.create.calculate');
    Route::get('kayitli-bilgi', 'OrganisationController@billingInformation')->name('billing.information');
});

Route::prefix('geo')->group(function () {
    Route::get('countries', 'GeoController@countries')->name('geo.countries');
    Route::get('states', 'GeoController@states')->name('geo.states');
});

Route::get('intro/gec', 'HomeController@skipIntro')->name('intro.skip');

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
