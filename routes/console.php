<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\System\SystemController;

Artisan::command('check:upcoming_payments', function () {
    OrganisationController::checkUpcomingPayments();
})->describe('Ödemesi yaklaşan organizasyon işlemleri.');

Artisan::command('alarm:control', function () {
    SystemController::alarmControl();
})->describe('Alarmların kontrolü.');
