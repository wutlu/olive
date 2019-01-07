<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\PinController;
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

Artisan::command('forum:notification_trigger', function () {
	ForumController::threadFollowNotifications();
})->describe('Takip edilen konulara verilen cevaplar için e-posta bildirimleri.');
