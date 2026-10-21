<?php

namespace App\Console\Commands;

use App\Models\Rate;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**
 * Daily NBP exchange rates import (cron: docker/cron.d-depot, 09:00).
 * Moved here from a closure + global function in routes/console.php;
 * the command name and arguments are unchanged.
 */
class FetchRates extends Command
{
    protected $signature = 'fetch_rates {date=yesterday}';

    protected $description = 'Rates fetch from NBP';

    public function handle(): void
    {
        $this->comment('Fetching rates...');

        $date = new Carbon($this->argument('date'));

        // Every day from $date up to and including yesterday. This used to be
        // `Carbon::now()->diffInDays($date) > 0`, which never runs on Carbon 3:
        // diffInDays() is signed there, i.e. negative for dates in the past.
        while ($date->lt(Carbon::today())) {
            $this->comment('Fetching '.$date->toDateString());
            $this->fetchRates($date->toDateString());
            $date->addDay();
        }
    }

    private function fetchRates(string $date): void
    {
        $client = new Client();
        $res = $client->request('GET', 'http://api.nbp.pl/api/exchangerates/tables/a/'.$date, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);

        if ($res->getStatusCode() < 300) {
            $response = json_decode((string) $res->getBody());
            foreach ($response[0]->rates as $fetchedRate) {
                $rate = new Rate();
                $rate->currency = $fetchedRate->code;
                $rate->value = $fetchedRate->mid;
                $rate->date = $date;
                $rate->save();
            }
        }
    }
}
