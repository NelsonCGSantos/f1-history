<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\Lap;
use App\Models\Meeting;
use App\Models\Position;
use App\Models\Session;
use App\Models\Stint;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1EndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected Meeting $meeting;
    protected Session $raceSession;
    protected Driver $driverOne;
    protected Driver $driverTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSampleData();
    }

    protected function setUpSampleData(): void
    {
        $this->meeting = Meeting::create([
            'name'        => 'Sample Grand Prix',
            'season_year' => 2024,
            'location'    => 'Sample City',
            'country'     => 'Sampleland',
            'start_date'  => Carbon::parse('2024-03-01'),
            'end_date'    => Carbon::parse('2024-03-03'),
        ]);

        Session::create([
            'meeting_id'  => $this->meeting->id,
            'type'        => 'Practice',
            'session_key' => 1001,
            'start_time'  => Carbon::parse('2024-03-01 10:00:00'),
            'end_time'    => Carbon::parse('2024-03-01 11:00:00'),
        ]);

        $this->raceSession = Session::create([
            'meeting_id'  => $this->meeting->id,
            'type'        => 'RACE',
            'session_key' => 1002,
            'start_time'  => Carbon::parse('2024-03-02 14:00:00'),
            'end_time'    => Carbon::parse('2024-03-02 16:00:00'),
        ]);

        $this->driverOne = Driver::create([
            'driver_number' => 44,
            'name'          => 'Lewis Hamilton',
            'team_name'     => 'Mercedes',
            'nationality'   => 'GBR',
            'abbreviation'  => 'HAM',
        ]);

        $this->driverTwo = Driver::create([
            'driver_number' => 16,
            'name'          => 'Charles Leclerc',
            'team_name'     => 'Ferrari',
            'nationality'   => 'MON',
            'abbreviation'  => 'LEC',
        ]);

        Lap::create([
            'session_id'  => $this->raceSession->id,
            'driver_id'   => $this->driverOne->id,
            'lap_number'  => 1,
            'lap_time'    => 95.321,
            'sector_1_time' => 30.0,
            'sector_2_time' => 32.0,
            'sector_3_time' => 33.321,
        ]);

        Lap::create([
            'session_id'  => $this->raceSession->id,
            'driver_id'   => $this->driverTwo->id,
            'lap_number'  => 1,
            'lap_time'    => 96.654,
            'sector_1_time' => 30.5,
            'sector_2_time' => 32.2,
            'sector_3_time' => 33.954,
        ]);

        Stint::create([
            'session_id'        => $this->raceSession->id,
            'driver_id'         => $this->driverOne->id,
            'start_lap'         => 1,
            'end_lap'           => 20,
            'tire_compound'     => 'SOFT',
            'stint_number'      => 1,
            'tyre_age_at_start' => 3,
        ]);

        Position::create([
            'session_id' => $this->raceSession->id,
            'driver_id'  => $this->driverOne->id,
            'date'       => Carbon::parse('2024-03-02 16:00:00'),
            'position'   => 1,
        ]);

        Position::create([
            'session_id' => $this->raceSession->id,
            'driver_id'  => $this->driverTwo->id,
            'date'       => Carbon::parse('2024-03-02 15:59:00'),
            'position'   => 2,
        ]);
    }

    public function test_can_list_seasons(): void
    {
        $this->getJson('/api/v1/seasons')
            ->assertOk()
            ->assertJson(['data' => [2024]]);
    }

    public function test_can_show_season_detail(): void
    {
        $response = $this->getJson('/api/v1/seasons/2024')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'season_year',
                        'sessions' => [
                            [
                                'id',
                                'type',
                                'session_key',
                                'start_time',
                                'end_time',
                                'laps_count',
                                'positions_count',
                                'stints_count',
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.sessions.1.laps_count', 2);
    }

    public function test_can_show_race_summary(): void
    {
        $response = $this->getJson("/api/v1/races/{$this->meeting->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'classification' => [
                        [
                            'position',
                            'driver' => [
                                'id',
                                'driver_number',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.classification.0.driver.name', 'Lewis Hamilton');
    }

    public function test_can_show_race_stints(): void
    {
        $response = $this->getJson("/api/v1/races/{$this->meeting->id}/stints")
            ->assertOk()
            ->assertJsonStructure([
                'meeting' => ['id', 'name', 'season_year'],
                'sessions' => [
                    [
                        'id',
                        'type',
                        'stints' => [
                            [
                                'id',
                                'stint_number',
                                'driver' => [
                                    'name',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertJsonPath('sessions.0.stints.0.driver.name', 'Lewis Hamilton');
    }

    public function test_can_show_driver_profile(): void
    {
        $response = $this->getJson('/api/v1/drivers/44')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'driver_number',
                    'name',
                    'season_years',
                    'recent_results',
                ],
            ]);

        $response->assertJsonPath('data.season_years.0', 2024);
    }

    public function test_can_show_season_standings(): void
    {
        $response = $this->getJson('/api/v1/seasons/2024/standings')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'driver' => [
                            'id',
                            'name',
                        ],
                        'points',
                        'wins',
                        'podiums',
                        'results',
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.points', 25);
    }
}
