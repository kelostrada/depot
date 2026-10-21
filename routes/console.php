<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// fetch_rates (daily cron, see docker/cron.d-depot) lives in
// app/Console/Commands/FetchRates.php.

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
