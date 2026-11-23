<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rappels des créneaux Conférences & Lounge (J-7 support, veille test technique).
Schedule::command('slots:send-reminders')
    ->dailyAt('09:00')
    ->timezone('Africa/Casablanca')
    ->withoutOverlapping();
