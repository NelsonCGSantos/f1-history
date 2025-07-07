<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Meeting;

class ImportF1Data extends Command
{
    protected $signature = 'f1:import {season}';
    protected $description = 'Import F1 meeting data from OpenF1 API';

    Public function handle()
{
    $season = $this->argument('season');
    $this->info("Importing meetings for season: $season");

    $url = 'https://api.openf1.org/v1/meetings';

    try {
        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            $this->error("❌ Failed to fetch data from OpenF1 API.");
            return;
        }

        $meetings = $response->json();

        $filtered = array_filter($meetings, function ($meeting) use ($season) {
            return isset($meeting['season_year']) && $meeting['season_year'] == $season;
        });

        $count = 0;

       foreach ($meetings as $meeting) {
    if ($meeting['year'] == $season) {
        Meeting::updateOrCreate(
            ['id' => $meeting['meeting_key']],
            [
                'name' => $meeting['meeting_name'],
                'season_year' => $meeting['year'],
                'location' => $meeting['location'],
                'country' => $meeting['country_name'],
                'start_date' => $meeting['date_start'],
                'end_date' => $meeting['date_start'], // assuming no end_date from API
            ]
        );
        $count++;
    }
}


        $this->info("✅ Imported $count meetings.");
    } catch (\Exception $e) {
        $this->error("❌ Exception: " . $e->getMessage());
    }
}
}
