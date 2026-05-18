<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\NotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:due-soon', function (NotificationService $notificationService) {
    $dueSoonCreated = $notificationService->sendDueSoonNotifications();
    $overdueCreated = $notificationService->sendOverdueNotifications();

    $this->info("Created {$dueSoonCreated} due soon notifications.");
    $this->info("Created {$overdueCreated} overdue notifications.");
})->purpose('Send in-app notifications for tasks nearing or past their deadline');

Schedule::command('notifications:due-soon')->hourly();
