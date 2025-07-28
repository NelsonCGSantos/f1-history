<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\F1DataImporter;
use App\Models\Session;

class ImportF1Data extends Command
{
    protected $signature = 'f1:import {season}';
    protected $description = 'Import F1 data for a given season from OpenF1 API';

    public function handle()
    {
        $season = $this->argument('season');
        $this->info("Starting import for season: $season");

        try {
            $importer = new F1DataImporter();

            // 1) Meetings & Sessions
            $this->info("Importing Meetings & Sessions...");
            $meetingsCount = $importer->importMeetingsWithSessions($season);
            $this->info("✅ Imported $meetingsCount meetings\n");

            // 2) Drivers
            $this->info("Importing Drivers...");
            $driversCount = $importer->importDrivers($season);
            $this->info("✅ Imported $driversCount drivers\n");

            // 3) Laps
            $this->info("Importing Laps...");
            $lapsCount = $importer->importLapsForSeason($season);
            $this->info("✅ Imported $lapsCount lap records\n");

            // 4) Positions
            $this->info("Importing Positions...");
            $positionsCount = $importer->importPositionsForSeason($season);
            $this->info("✅ Imported $positionsCount position records\n");

            // Prepare only RACE and QUALIFY sessions for time-sensitive imports
            $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))
                ->whereIn('type', ['RACE', 'QUALIFY'])
                ->get();

            // 5) Stints with progress bar
            $this->info("Importing Stints for {$sessions->count()} sessions...");
            $stintsBar = $this->output->createProgressBar($sessions->count());
            $stintsBar->start();

            $stintsCount = 0;
            foreach ($sessions as $session) {
                $stintsCount += $importer->importStintsForSession($session->session_key);
                $stintsBar->advance();
            }
            $stintsBar->finish();
            $this->newLine();
            $this->info("✅ Imported $stintsCount stint records\n");

            $this->info("Import complete for season: $season!");

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}
