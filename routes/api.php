<?php

Route::post('payment/callback', 'OrganisationController@paymentCallback')->name('organisation.invoice.payment.callback');

Route::prefix('url')->group(function () {
    Route::get('{key}', 'LinkController@get')->name('link.get');
});
