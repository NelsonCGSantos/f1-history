<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Driver;

class F1DataImporter
{
    public function importMeetingsWithSessions($season)
    {
        $url = 'https://api.openf1.org/v1/meetings';
        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch meetings');
        }

        $meetings = $response->json();
        $imported = 0;

        foreach ($meetings as $meeting) {
            if (($meeting['year'] ?? null) != $season) {
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

    private function importSessionsForMeeting($meetingId, $meetingKey)
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
            if (!isset($session['session_key'])) {
                Log::warning("Skipping session with missing key for meeting {$meetingKey}");
                continue;
            }

            Session::updateOrCreate(
                ['session_key' => $session['session_key']],
                [
                    'meeting_id' => $meetingId,
                    'type'       => $session['session_type'] ?? null,
                    'start_time' => $session['date_start'] ?? null,
                    'end_time'   => $session['date_end'] ?? null,
                ]
            );
        }
    }

    public function importDrivers($season)
    {
        // Fetch all sessions for the given season
        $sessions = Session::whereHas('meeting', function ($query) use ($season) {
            $query->where('season_year', $season);
        })->get();

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
                        'name'         => $driver['full_name'] ?? trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')),
                        'team_name'    => $driver['team_name'] ?? null,
                        'nationality'  => $driver['country_code'] ?? null,
                        'abbreviation' => $driver['name_acronym'] ?? null,
                    ]
                );

                $seenDriverNumbers[$driverNumber] = true;
                $count++;
            }
        }

        return $count;
    }
}
