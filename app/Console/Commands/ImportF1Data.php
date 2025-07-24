<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\F1DataImporter;
use App\Models\Session;

class ImportF1Data extends Command
{
    protected $signature = 'f1:import {season}';
    protected $description = 'Import F1 meetings, sessions, drivers, laps, positions, and stints from OpenF1 API';

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

            $stintsCount = 0;
            $sessions = Session::whereHas('meeting', function ($query) use ($season) {
                $query->where('season_year', $season);
            })->get();

            foreach ($sessions as $session) {
                $stintsCount += $importer->importStintsForSession($session->session_key);
            }

            $this->info("✅ Imported $meetingsCount meetings (with sessions)");
            $this->info("✅ Imported $driversCount drivers");
            $this->info("✅ Imported $lapsCount lap records");
            $this->info("✅ Imported $positionsCount position records");
            $this->info("✅ Imported $stintsCount stint records");

        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}
