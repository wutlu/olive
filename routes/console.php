<?php

use App\Http\Controllers\OrganisationController;

Artisan::command('check:upcoming_payments', function () {
    $this->comment(OrganisationController::checkUpcomingPayments());
})->describe('Ödemesi yaklaşan organizasyon işlemleri.');
