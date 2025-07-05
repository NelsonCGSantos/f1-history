<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ErgastClient;
use App\Models\Season;
use App\Models\Circuit;
use App\Models\Race;
use App\Models\Driver;
use App\Models\Constructor;
use App\Models\Result;
use App\Models\Standing; // if you import standings here

class F1Import extends Command
{
    protected $signature = 'f1:import {season?}';
    protected $description = 'Import F1 data for a given season';

    public function handle(ErgastClient $ergast)
    {
        $year = $this->argument('season') ?: date('Y');

        $this->info("Starting import for season {$year}…");

        // 1) Upsert the season
        $season = Season::updateOrCreate(
            ['year' => $year],
            ['champion_driver_id' => null] // we can fill this later
        );

        // 2) Fetch and loop races
        $data = $ergast->fetch("{$year}/results")['RaceTable']['Races'];
        foreach ($data as $raceData) {
            // Upsert circuit
            $circuit = Circuit::updateOrCreate(
                ['circuit_id' => $raceData['Circuit']['circuitId']],
                [
                  'name'     => $raceData['Circuit']['circuitName'],
                  'location' => $raceData['Circuit']['Location']['locality'],
                  'lat'      => $raceData['Circuit']['Location']['lat'],
                  'lng'      => $raceData['Circuit']['Location']['long'],
                ]
            );

            // Upsert race
            $race = Race::updateOrCreate(
                ['season_id' => $season->id, 'circuit_id' => $circuit->id, 'name' => $raceData['raceName']],
                ['date' => $raceData['date']]
            );

            $this->info("Imported race: {$race->name} on {$race->date}");

            // 3) Loop results
            foreach ($raceData['Results'] as $res) {
                // Driver
                $driver = Driver::updateOrCreate(
                    ['driver_id' => $res['Driver']['driverId']],
                    [
                      'given_name'  => $res['Driver']['givenName'],
                      'family_name' => $res['Driver']['familyName'],
                      'date_of_birth' => $res['Driver']['dateOfBirth'],
                      'nationality' => $res['Driver']['nationality'],
                    ]
                );

                // Constructor (team)
                $constructor = Constructor::updateOrCreate(
                    ['constructor_id' => $res['Constructor']['constructorId']],
                    [
                      'name'        => $res['Constructor']['name'],
                      'nationality' => $res['Constructor']['nationality'],
                    ]
                );

                // Result row
                Result::updateOrCreate(
                    [
                      'race_id'        => $race->id,
                      'driver_id'      => $driver->driver_id,
                    ],
                    [
                      'constructor_id' => $constructor->constructor_id,
                      'grid'           => $res['grid'],
                      'position'       => intval($res['position']),
                      'laps'           => $res['laps'],
                      'status'         => $res['status'],
                      'time'           => $res['Time']['time'] ?? null,
                    ]
                );
            }
        }

        // 4) (Optional) Import standings
        $standingsData = $ergast->fetch("{$year}/driverStandings")['StandingsTable']['StandingsLists'][0]['DriverStandings'];
        foreach ($standingsData as $st) {
            Standing::updateOrCreate(
                ['season_id' => $season->id, 'driver_id' => $st['Driver']['driverId']],
                ['position' => $st['position'], 'points' => $st['points']]
            );
        }

        $this->info("Finished importing season {$year}.");
    }
}
