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
     * Simple caches for model lookups to avoid repeated queries.
     */
    protected array $driverCache = [];
    protected array $sessionCache = [];

    /**
     * Maximum number of lap rows to persist per upsert call.
     */
    protected int $lapUpsertChunkSize = 500;

    /**
     * Get driver ID by driver_number, with in-memory caching.
     */
    protected function getDriverId(int $driverNumber): ?int
    {
        if (array_key_exists($driverNumber, $this->driverCache)) {
            return $this->driverCache[$driverNumber];
        }
        $driver = Driver::where('driver_number', $driverNumber)->first();
        $id = $driver?->id;
        $this->driverCache[$driverNumber] = $id;
        return $id;
    }

    /**
     * Get session ID by session_key, with in-memory caching.
     */
    protected function getSessionId($sessionKey): ?int
    {
        if (array_key_exists($sessionKey, $this->sessionCache)) {
            return $this->sessionCache[$sessionKey];
        }
        $session = Session::where('session_key', $sessionKey)->first();
        $id = $session?->id;
        $this->sessionCache[$sessionKey] = $id;
        return $id;
    }

    /**
     * Fetch an OpenF1 endpoint with 1-hour file-based caching,
     * but only cache on a successful JSON array fetch.
     */
    protected function fetchJson(string $path): array
    {
        $cacheKey = "openf1:{$path}";
        $store    = Cache::store('file');

        try {
            $response = Http::retry(2, 100)
                            ->timeout(30)
                            ->get("https://api.openf1.org/v1/{$path}");

            if ($response->ok()) {
                $payload = $response->json();
                if (is_array($payload)) {
                    $store->put($cacheKey, $payload, now()->addHour());
                    return $payload;
                }
            } else {
                Log::warning("OpenF1 returned {$response->status()} for {$path}");
            }
        } catch (\Throwable $e) {
            Log::warning("HTTP fetch error for {$path}: {$e->getMessage()}");
        }

        return $store->get($cacheKey, []);
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
                    'name'                 => $meeting['meeting_name'],
                    'season_year'          => $meeting['year'],
                    'location'             => $meeting['location']       ?? null,
                    'country'              => $meeting['country_name']   ?? null,
                    'start_date'           => $meeting['date_start']     ?? null,
                    // Use date_end if provided by API, else fallback to date_start
                    'end_date'             => $meeting['date_end']       ?? $meeting['date_start'] ?? null,
                ]
            );

            $sessions = $this->fetchJson("sessions?meeting_key={$meeting['meeting_key']}");
            foreach ($sessions as $sessionData) {
                if (empty($sessionData['session_key'])) {
                    Log::warning("Skipping malformed session for meeting {$meeting['meeting_key']}");
                    continue;
                }

                Session::updateOrCreate(
                    ['session_key' => $sessionData['session_key']],
                    [
                        'meeting_id' => $gp->id,
                        'type'       => $sessionData['session_type'] ?? null,
                        'start_time' => $sessionData['date_start']   ?? null,
                        'end_time'   => $sessionData['date_end']     ?? null,
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
        $seen     = [];
        $count    = 0;

        foreach ($sessions as $session) {
            $drivers = $this->fetchJson("drivers?session_key={$session->session_key}");
            foreach ($drivers as $driverData) {
                $num = $driverData['driver_number'] ?? null;
                if (!$num || isset($seen[$num])) {
                    continue;
                }

                Driver::updateOrCreate(
                    ['driver_number' => $num],
                    [
                        'name'         => $driverData['full_name'] ?? trim((($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''))),
                        'team_name'    => $driverData['team_name']     ?? null,
                        'nationality'  => $driverData['country_code']  ?? null,
                        'abbreviation' => $driverData['name_acronym']  ?? null,
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
        $sessionsMap = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))
                              ->pluck('id', 'session_key')
                              ->toArray();
        $driversMap  = Driver::pluck('id', 'driver_number')->toArray();
        $total       = 0;

        foreach ($sessionsMap as $sessionKey => $sessionId) {
            $rows = [];
            $now  = Carbon::now();
            $laps = $this->fetchJson("laps?session_key={$sessionKey}");

            foreach ($laps as $lap) {
                $num = $lap['driver_number'] ?? null;
                if (!isset($lap['lap_number'], $num, $lap['lap_duration']) || !isset($driversMap[$num])) {
                    continue;
                }

                $rows[] = [
                    'session_id'        => $sessionId,
                    'driver_id'         => $driversMap[$num],
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

                if (count($rows) >= $this->lapUpsertChunkSize) {
                    $this->upsertLapRows($rows);
                    $total += count($rows);
                    $rows = [];
                }
            }

            if ($rows) {
                $this->upsertLapRows($rows);
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
        $sessionsMap = Session::whereHas('meeting', fn($q) => $q->where('season_year', $season))
                              ->pluck('id', 'session_key')
                              ->toArray();
        $driversMap  = Driver::pluck('id', 'driver_number')->toArray();
        $count       = 0;

        foreach ($sessionsMap as $sessionKey => $sessionId) {
            $positions = $this->fetchJson("position?session_key={$sessionKey}");
            foreach ($positions as $pos) {
                $num = $pos['driver_number'] ?? null;
                if (!isset($pos['position'], $pos['date']) || !isset($driversMap[$num])) {
                    continue;
                }

                $when = Carbon::parse($pos['date']);
                Position::updateOrCreate(
                    ['session_id' => $sessionId, 'driver_id' => $driversMap[$num], 'date' => $when],
                    ['position'   => $pos['position']]
                );

                $count++;
            }
        }

        return $count;
    }

    /**
     * Import stints for a given session, skipping on any fetch issues.
     */
    public function importStintsForSession($sessionKey): int
    {
        $sessionId = $this->getSessionId($sessionKey);
        if (!$sessionId) {
            Log::warning("Session {$sessionKey} not found for stint import.");
            return 0;
        }

        $stints   = $this->fetchJson("stints?session_key={$sessionKey}");
        $count    = 0;

        foreach ($stints as $entry) {
            $num = $entry['driver_number'] ?? null;
            $drvId = $this->getDriverId($num);
            if (
                !$drvId ||
                !isset($entry['lap_start'], $entry['lap_end'], $entry['compound'], $entry['stint_number'], $entry['tyre_age_at_start'])
            ) {
                continue;
            }

            Stint::updateOrCreate(
                ['session_id' => $sessionId, 'driver_id' => $drvId, 'stint_number' => $entry['stint_number']],
                [
                    'start_lap'         => $entry['lap_start'],
                    'end_lap'           => $entry['lap_end'],
                    'tire_compound'     => $entry['compound'],
                    'tyre_age_at_start' => $entry['tyre_age_at_start'],
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Persist lap rows using an upsert operation.
     */
    protected function upsertLapRows(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        Lap::upsert(
            $rows,
            ['session_id', 'driver_id', 'lap_number'],
            [
                'lap_time', 'sector_1_time', 'sector_2_time', 'sector_3_time',
                'i1_speed', 'i2_speed', 'speed_trap', 'is_pit_out',
                'segments_sector_1', 'segments_sector_2', 'segments_sector_3',
                'updated_at',
            ]
        );
    }
}
