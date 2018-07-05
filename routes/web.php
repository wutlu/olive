<?php

Route::get('/', 'HomeController@index')->name('home');
Route::get('panel', 'HomeController@dashboard')->name('dashboard');
Route::get('aktiviteler', 'HomeController@activity')->name('dashboard.activities');

Route::match([ 'get', 'post' ], 'baslayin/{step?}', 'OrganisationController@create')->name('organisation.create')->where('step', '(1|2|3|4)');
Route::post('baslayin/hesap', 'OrganisationController@calculate')->name('organisation.create.calculate');
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
