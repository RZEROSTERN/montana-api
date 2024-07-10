<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TeamTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['-vvv' => true]);
    }

    public function test_create_team(): void
    {
        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $response->assertStatus(200);
    }

    public function test_get_teams_by_captain(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/teams');

        $response->assertStatus(200);
    }

    public function test_get_team(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/teams/1');

        $response->assertStatus(200);
    }

    public function test_update_team(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $body = [
            'team_name' => 'Team Rex 2',
            'foundation_date' => '2023-08-21',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->put('/api/teams/1', $body);

        $response->assertStatus(200);
    }

    public function test_delete_team(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->delete('/api/teams/1');

        $response->assertStatus(200);
    }
}
