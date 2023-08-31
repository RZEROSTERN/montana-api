<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TeamMemberTest extends TestCase
{
    public $faker;
    public $name;
    public $email;
    public $password;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();

        $this->name = $this->faker->name();
        $this->password = 'password';
        $this->email = $this->faker->email();

        Artisan::call('migrate:fresh', ['-vvv' => true]);
        Artisan::call('passport:install', ['-vvv' => true]);
    }

    public function test_get_team_members(): void
    {
        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/teams/1/members');

        $response->assertStatus(200);
    }

    public function test_add_member_to_team(): void
    {
        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $body = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'c_password' => $this->password
        ];

        $this->json('POST', '/api/register', $body);

        $body = [
            'user_id' => 2,
            'team_id' => 1,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams/members/add', $body);

        $response->assertStatus(200);
    }

    public function test_drop_member_from_team(): void
    {
        $body = [
            'team_name' => 'Team Rex',
            'foundation_date' => '2023-08-19',
            'brochure' => 'Lorem ipsum dolor sit amet',
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams', $body);

        $body = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'c_password' => $this->password
        ];

        $this->json('POST', '/api/register', $body);

        $body = [
            'user_id' => 2,
            'team_id' => 1,
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams/members/add', $body);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/teams/members/drop', $body);

        $response->assertStatus(200);
    }
}
