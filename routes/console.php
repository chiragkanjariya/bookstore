<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cron jobs for order status updates
Schedule::command('orders:update-shipped')->hourly();
Schedule::command('orders:update-delivered')->hourly();
