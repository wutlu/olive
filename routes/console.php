<?php

use App\Http\Controllers\OrganisationController;

Artisan::command('check:upcoming_payments', function () {
    OrganisationController::checkUpcomingPayments();
})->describe('Ödemesi yaklaşan organizasyon işlemleri.');
