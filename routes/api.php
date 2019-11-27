<?php

Route::domain('olive.'.config('app.domain'))->group(function () {
    Route::post('payment/callback', 'OrganisationController@paymentCallback')->name('organisation.invoice.payment.callback');
});

Route::domain(config('app.domain'))->group(function () {
    Route::prefix('url')->group(function () {
        Route::get('{key}', 'LinkController@get')->name('link.get');
    });
});
