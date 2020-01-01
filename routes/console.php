<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Crawlers\MediaController;
use App\Http\Controllers\Crawlers\BlogController;

use App\Console\Commands\Sentiment;

Artisan::command('check:upcoming_payments', function () {
    OrganisationController::checkUpcomingPayments();
})->describe('Ödemesi yaklaşan organizasyon işlemleri.');

Artisan::command('alarm:control', function () {
    MonitorController::alarmControl();
})->describe('Alarmların kontrolü.');

Artisan::command('trigger:pdf:archives', function () {
	ArchiveController::pdfTrigger();
})->describe('PDF çıktı almak için arşivleri tetikler.');

Artisan::command('forum:notification_trigger', function () {
	ForumController::threadFollowNotifications();
})->describe('Takip edilen konulara verilen cevaplar için e-posta bildirimleri.');

Artisan::command('update:crawler_counts', function () {
	MediaController::counter();
	BlogController::counter();
})->describe('Veritabanındaki döküman sayılarını SQL\'e alır.');
