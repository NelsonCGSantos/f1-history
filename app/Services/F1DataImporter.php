<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Driver;
use App\Models\Lap;
use App\Models\Position;
use App\Models\Stint;

class F1DataImporter
{
    /**
     * Fetch an OpenF1 endpoint with 1-hour file-based caching.
     *
     * @param  string  $path
     * @return array
     */
    protected function fetchJson(string $path): array
{
    $cacheKey = "openf1:{$path}";

    return Cache::store('file')->remember(
        $cacheKey,
        now()->addHour(),
        function () use ($path) {
            try {
                // retry up to 2 times, wait 100ms between attempts
                $response = Http::retry(2, 100)
                                ->timeout(30)
                                ->get("https://api.openf1.org/v1/{$path}");
            } catch (\Throwable $e) {
                Log::warning("HTTP fetch failed for {$path}: {$e->getMessage()}");
                return [];
            }

            if (! $response->ok()) {
                Log::warning("Non-200 response for {$path}: {$response->status()}");
                return [];
            }

            $json = $response->json();
            return is_array($json) ? $json : [];
        }
    );
}

    /**
     * Import meetings and their sessions for a given season.
     */
    public function importMeetingsWithSessions(int $season): int
    {
        $meetings = $this->fetchJson('meetings');
        $imported = 0;

        foreach ($meetings as $meeting) {
            if (($meeting['year'] ?? null) !== $season) {
                continue;
            }

            $gp = Meeting::updateOrCreate(
                ['id' => $meeting['meeting_key']],
                [
                    'name'        => $meeting['meeting_name'],
                    'season_year' => $meeting['year'],
                    'location'    => $meeting['location']      ?? null,
                    'country'     => $meeting['country_name']  ?? null,
                    'start_date'  => $meeting['date_start']    ?? null,
                    'end_date'    => $meeting['date_start']    ?? null,
                ]
            );

            $sessions = $this->fetchJson("sessions?meeting_key={$meeting['meeting_key']}");
            foreach ($sessions as $session) {
                if (empty($session['session_key'])) {
                    Log::warning("Skipping malformed session for meeting {$meeting['meeting_key']}");
                    continue;
                }

                Session::updateOrCreate(
                    ['session_key' => $session['session_key']],
                    [
                        'meeting_id' => $gp->id,
                        'type'       => $session['session_type'] ?? null,
                        'start_time' => $session['date_start']   ?? null,
                        'end_time'   => $session['date_end']     ?? null,
                    ]
                );
            }

            $imported++;
        }

        return $imported;
    }

    /**
     * Import unique drivers for every session in the season.
     */
    public function importDrivers(int $season): int
    {
        $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))->get();
        $seen = [];
        $count = 0;

        foreach ($sessions as $session) {
            $drivers = $this->fetchJson("drivers?session_key={$session->session_key}");
            foreach ($drivers as $driver) {
                $num = $driver['driver_number'] ?? null;
                if (!$num || isset($seen[$num])) {
                    continue;
                }

                Driver::updateOrCreate(
                    ['driver_number' => $num],
                    [
                        'name'         => $driver['full_name'] ?? trim((($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''))),
                        'team_name'    => $driver['team_name']     ?? null,
                        'nationality'  => $driver['country_code']  ?? null,
                        'abbreviation' => $driver['name_acronym']  ?? null,
                    ]
                );

                $seen[$num] = true;
                $count++;
            }
        }

        return $count;
    }

    /**
     * Import all lap times for every session in a given season using batch upsert.
     */
    public function importLapsForSeason(int $season): int
    {
        $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))->get();
        $total = 0;

        foreach ($sessions as $session) {
            $rows = [];
            $now  = Carbon::now();
            $laps = $this->fetchJson("laps?session_key={$session->session_key}");

            foreach ($laps as $lap) {
                if (!isset($lap['lap_number'], $lap['driver_number'], $lap['lap_duration'])) {
                    continue;
                }

                $drv = Driver::where('driver_number', $lap['driver_number'])->first();
                if (!$drv) {
                    Log::warning("Driver #{$lap['driver_number']} not found for lap import.");
                    continue;
                }

                $rows[] = [
                    'session_id'        => $session->id,
                    'driver_id'         => $drv->id,
                    'lap_number'        => $lap['lap_number'],
                    'lap_time'          => $lap['lap_duration'],
                    'sector_1_time'     => $lap['duration_sector_1'] ?? null,
                    'sector_2_time'     => $lap['duration_sector_2'] ?? null,
                    'sector_3_time'     => $lap['duration_sector_3'] ?? null,
                    'i1_speed'          => $lap['i1_speed'] ?? null,
                    'i2_speed'          => $lap['i2_speed'] ?? null,
                    'speed_trap'        => $lap['st_speed'] ?? null,
                    'is_pit_out'        => $lap['is_pit_out_lap'] ?? false,
                    'segments_sector_1' => json_encode($lap['segments_sector_1'] ?? []),
                    'segments_sector_2' => json_encode($lap['segments_sector_2'] ?? []),
                    'segments_sector_3' => json_encode($lap['segments_sector_3'] ?? []),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            if (!empty($rows)) {
                Lap::upsert(
                    $rows,
                    ['session_id','driver_id','lap_number'],
                    ['lap_time','sector_1_time','sector_2_time','sector_3_time',
                     'i1_speed','i2_speed','speed_trap','is_pit_out',
                     'segments_sector_1','segments_sector_2','segments_sector_3',
                     'updated_at']
                );
                $total += count($rows);
            }
        }

        return $total;
    }

    /**
     * Import position data for every session in a given season.
     */
    public function importPositionsForSeason(int $season): int
    {
        $sessions = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))->get();
        $count = 0;

        foreach ($sessions as $session) {
            $positions = $this->fetchJson("position?session_key={$session->session_key}");
            foreach ($positions as $pos) {
                if (!isset($pos['driver_number'], $pos['position'], $pos['date'])) {
                    continue;
                }

                $drv = Driver::where('driver_number', $pos['driver_number'])->first();
                if (!$drv) {
                    Log::warning("Driver #{$pos['driver_number']} not found for position import.");
                    continue;
                }

                $when = Carbon::parse($pos['date']);

                Position::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'driver_id'  => $drv->id,
                        'date'       => $when,
                    ],
                    ['position' => $pos['position']]
                );

                $count++;
            }
        }

        return $count;
    }

    public function importStintsForSession($sessionKey)
{
    $url = "https://api.openf1.org/v1/stints?session_key={$sessionKey}";
    $response = Http::timeout(10)->get($url);

    if ($response->failed()) {
        throw new \Exception("Failed to fetch stints for session $sessionKey");
    }

    $stints = $response->json();
    $count = 0;

    foreach ($stints as $entry) {
    $driver = Driver::where('driver_number', $entry['driver_number'])->first();
    $session = Session::where('session_key', $entry['session_key'])->first();

    // Skip if any required data is missing
    if (
        !$driver || !$session ||
        !isset($entry['lap_start'], $entry['lap_end'], $entry['compound'], $entry['stint_number'], $entry['tyre_age_at_start'])
    ) {
        continue;
    }

    Stint::updateOrCreate(
        [
            'session_id' => $session->id,
            'driver_id' => $driver->id,
            'stint_number' => $entry['stint_number'],
        ],
        [
            'start_lap' => $entry['lap_start'],
            'end_lap' => $entry['lap_end'],
            'tire_compound' => $entry['compound'],
            'tyre_age_at_start' => $entry['tyre_age_at_start'],
        ]
    );

    $count++;
}


    return $count;
}

}
