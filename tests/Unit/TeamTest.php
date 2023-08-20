<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TeamTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh', ['-vvv' => true]);
        Artisan::call('passport:install', ['-vvv' => true]);
    }

    public function test_create_team(): void
    {
        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $response->assertStatus(200);
    }
}
