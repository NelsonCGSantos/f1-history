<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\F1DataImporter;

class ImportF1Data extends Command
{
    protected $signature = 'f1:import {season}';
    protected $description = 'Import F1 meeting and session data from OpenF1 API';

    public function handle()
{
    $season = $this->argument('season');
    $this->info("Importing meetings and sessions for season: $season");

    try {
        $importer = new F1DataImporter();

        $meetingsCount = $importer->importMeetingsWithSessions($season);
        $driversCount = $importer->importDrivers($season);


        $this->info("✅ Imported $meetingsCount meetings (with sessions)");
        $this->info("✅ Imported $driversCount drivers");
    } catch (\Exception $e) {
        $this->error("❌ Exception: " . $e->getMessage());
    }
}
}
