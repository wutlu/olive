<?php

Route::post('payment/callback', 'OrganisationController@paymentCallback')->name('organisation.invoice.payment.callback');
