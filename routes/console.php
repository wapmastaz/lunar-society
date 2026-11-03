<?php

use App\Services\Registry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('lunar:expire-holds', function (Registry $registry) {
    $this->info($registry->expireHolds().' holds expired.');
})->purpose('Release founder slots held by unpaid orders');

Schedule::command('lunar:expire-holds')->everyMinute();
