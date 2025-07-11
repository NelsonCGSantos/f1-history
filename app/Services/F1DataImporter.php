<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Driver;
use App\Models\Lap;

class F1DataImporter
{
    /**
     * Import meetings(GPs) and then their sessions for a given season.
     */
    public function importMeetingsWithSessions(int $season): int
    {
        $url = 'https://api.openf1.org/v1/meetings';
        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch meetings');
        }

        $meetings = $response->json();
        $imported = 0;

        foreach ($meetings as $meeting) {
            if (($meeting['year'] ?? null) !== $season) {
                continue;
            }

            $newMeeting = Meeting::updateOrCreate(
                ['id' => $meeting['meeting_key']],
                [
                    'name' => $meeting['meeting_name'],
                    'season_year' => $meeting['year'],
                    'location' => $meeting['location'] ?? null,
                    'country' => $meeting['country_name'] ?? null,
                    'start_date' => $meeting['date_start'] ?? null,
                    'end_date' => $meeting['date_start'] ?? null,
                ]
            );

            $this->importSessionsForMeeting($newMeeting->id, $meeting['meeting_key']);
            $imported++;
        }

        return $imported;
    }

    /**
     * Import sessions for a specific meeting (GP).
     */
    private function importSessionsForMeeting(int $meetingId, int $meetingKey): void
    {
        $url = "https://api.openf1.org/v1/sessions?meeting_key={$meetingKey}";
        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            Log::error("API error: Failed to fetch sessions for meeting {$meetingKey}");
            return;
        }

        $sessions = $response->json();
        if (!is_array($sessions)) {
            Log::error("Unexpected sessions format for meeting {$meetingKey}: " . gettype($sessions));
            return;
        }
        if (empty($sessions)) {
            Log::info("No sessions found for meeting {$meetingKey}");
            return;
        }

        foreach ($sessions as $session) {
            if (empty($session['session_key'])) {
                Log::warning("Skipping session with missing key for meeting {$meetingKey}");
                continue;
            }

            Session::updateOrCreate(
                ['session_key' => $session['session_key']],
                [
                    'meeting_id' => $meetingId,
                    'type' => $session['session_type'] ?? null,
                    'start_time' => $session['date_start'] ?? null,
                    'end_time' => $session['date_end'] ?? null,
                ]
            );
        }
    }

    /**
     * Import unique drivers for every session in the season.
     */
    public function importDrivers(int $season): int
    {
        $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))->get();
        $seenDriverNumbers = [];
        $count = 0;

        foreach ($sessions as $session) {
            $sessionKey = $session->session_key;
            $url = "https://api.openf1.org/v1/drivers?session_key={$sessionKey}";
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                Log::warning("Failed to fetch drivers for session {$sessionKey}");
                continue;
            }

            $drivers = $response->json();
            if (!is_array($drivers)) {
                Log::warning("Unexpected drivers format for session {$sessionKey}: " . gettype($drivers));
                continue;
            }

            foreach ($drivers as $driver) {
                $driverNumber = $driver['driver_number'] ?? null;
                if (!$driverNumber || isset($seenDriverNumbers[$driverNumber])) {
                    continue;
                }

                Driver::updateOrCreate(
                    ['driver_number' => $driverNumber],
                    [
                        'name' => $driver['full_name'] ?? trim((($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''))),
                        'team_name' => $driver['team_name'] ?? null,
                        'nationality' => $driver['country_code'] ?? null,
                        'abbreviation' => $driver['name_acronym'] ?? null,
                    ]
                );

                $seenDriverNumbers[$driverNumber] = true;
                $count++;
            }
        }

        return $count;
    }

    /**
 * Import all lap times and extra details for every session in a given season.
 */
public function importLapsForSeason(int $season): int
{
    $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))
                       ->get();

    $imported = 0;

    foreach ($sessions as $session) {
        $key = $session->session_key;
        $response = Http::timeout(10)->get("https://api.openf1.org/v1/laps?session_key={$key}");

        if ($response->failed()) {
            Log::warning("Failed to fetch laps for session {$key}");
            continue;
        }

        $laps = $response->json();
        if (!is_array($laps)) {
            Log::warning("Unexpected laps format for session {$key}");
            continue;
        }

        foreach ($laps as $lap) {
            if (!isset($lap['lap_number'], $lap['driver_number'], $lap['lap_duration'])) {
                Log::warning("Skipping malformed lap for session {$key}: " . json_encode($lap));
                continue;
            }

            // **LOOK UP the real Eloquent driver record**
            $driver = Driver::where('driver_number', $lap['driver_number'])->first();
            if (! $driver) {
                Log::warning("Driver #{$lap['driver_number']} not found — skipping lap.");
                continue;
            }

            Lap::updateOrCreate(
                [
                  'session_id' => $session->id,
                  'driver_id'  => $driver->id,              // ← use the PK, not driver_number
                  'lap_number' => $lap['lap_number'],
                ],
                [
                  'lap_time'        => $lap['lap_duration'],
                  'sector_1_time'   => $lap['duration_sector_1']      ?? null,
                  'sector_2_time'   => $lap['duration_sector_2']      ?? null,
                  'sector_3_time'   => $lap['duration_sector_3']      ?? null,
                  'i1_speed'        => $lap['i1_speed']              ?? null,
                  'i2_speed'        => $lap['i2_speed']              ?? null,
                  'speed_trap'      => $lap['st_speed']              ?? null,
                  'is_pit_out'      => $lap['is_pit_out_lap']        ?? false,
                  'segments_sector_1'=> json_encode($lap['segments_sector_1'] ?? []),
                  'segments_sector_2'=> json_encode($lap['segments_sector_2'] ?? []),
                  'segments_sector_3'=> json_encode($lap['segments_sector_3'] ?? []),
                ]
            );

            $imported++;
        }
    }

    return $imported;
}

}
