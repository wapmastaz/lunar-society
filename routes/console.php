<?php

use App\Services\Registry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('lunar:expire-holds', function (Registry $registry) {
    $this->info($registry->expireHolds().' holds expired.');
})->purpose('Release founder slots held by unpaid orders');

Schedule::command('lunar:expire-holds')->everyMinute();

// Shared hosting can't keep a queue worker running, so the every-minute
// cron drains the queue instead (confirmation emails, PDFs, Beehiiv).
if (config('lunar.queue_via_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
        ->everyMinute()
        ->withoutOverlapping(5);
}
