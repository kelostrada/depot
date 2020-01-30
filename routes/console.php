<?php

use Illuminate\Foundation\Inspiring;
use GuzzleHttp\Client;
use Carbon\Carbon;
use \App\Models\Rate;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

function fetchRates($date)
{
    $client = new Client();
    $res = $client->request('GET', 'http://api.nbp.pl/api/exchangerates/tables/a/' . $date, [
        'headers' => [
            'Accept' => 'application/json'
        ],
        'http_errors' => false
    ]);

    if ($res->getStatusCode() < 300) {
        $body = $res->getBody();
        $response = json_decode($body);
        foreach ($response[0]->rates as $fetchedRate) {
            $rate = new Rate();
            $rate->currency = $fetchedRate->code;
            $rate->value = $fetchedRate->mid;
            $rate->date = $date;
            $rate->save();
        }
    }
}

Artisan::command('fetch_rates {date}', function($date) {
    $this->comment('Fetching rates...');

    $date = new Carbon($date);
    while(Carbon::now()->diffInDays($date) > 0) {
        $this->comment("Fetching " . $date->toDateString());
        fetchRates($date->toDateString());
        $date->addDay();
    }
})->describe('Rates fetch from NBP');
