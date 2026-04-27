<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\NotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:due-soon', function (NotificationService $notificationService) {
    $created = $notificationService->sendDueSoonNotifications();

    $this->info("Created {$created} due soon notifications.");
})->purpose('Send in-app notifications for tasks nearing their deadline');

Schedule::command('notifications:due-soon')->hourly();
