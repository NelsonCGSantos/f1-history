<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\F1DataImporter;

class ImportF1Data extends Command
{
    protected $signature = 'f1:import {season}';
    protected $description = 'Import F1 meetings, sessions, drivers, laps, and positions from OpenF1 API';
    public function handle()
{
    $season = $this->argument('season');
    $this->info("Importing meetings and sessions for season: $season");

    try {
        $importer = new F1DataImporter();

        $meetingsCount = $importer->importMeetingsWithSessions($season);
        $driversCount = $importer->importDrivers($season);
        $lapsCount = $importer->importLapsForSeason($season);
        $positionsCount = $importer->importPositionsForSeason($season);


        $this->info("✅ Imported $meetingsCount meetings (with sessions)");
        $this->info("✅ Imported $driversCount drivers");
        $this->info("✅ Imported $lapsCount lap records");
        $this->info("✅ Imported {$positionsCount} position records");
    } catch (\Exception $e) {
        $this->error("❌ Exception: " . $e->getMessage());
    }


}
}
