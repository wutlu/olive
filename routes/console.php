<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\RealTime\PinController;
use App\Console\Commands\Sentiment;

Artisan::command('check:upcoming_payments', function () {
    OrganisationController::checkUpcomingPayments();
})->describe('Ödemesi yaklaşan organizasyon işlemleri.');

Artisan::command('alarm:control', function () {
    SystemController::alarmControl();
})->describe('Alarmların kontrolü.');

Artisan::command('sentiment:update', function () {
    Sentiment::update();
})->describe('Duygu analizi listesini günceller.');

Artisan::command('trigger:pdf:pin_groups', function () {
	PinController::pdfTrigger();
})->describe('PDF çıktı almak için pin gruplarını tetikler.');
