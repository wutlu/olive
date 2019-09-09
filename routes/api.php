<?php

Route::domain('olive.'.config('app.domain'))->group(function () {
	Route::post('payment/callback', 'OrganisationController@paymentCallback')->name('organisation.invoice.payment.callback');
});
