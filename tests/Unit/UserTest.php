<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
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
        $this->email = 'valid@test.com';

        Artisan::call('migrate:fresh', ['-vvv' => true]);
        Artisan::call('passport:install', ['-vvv' => true]);
    }

    public function test_login_error_with_data_ok()
    {
        Artisan::call('db:seed', ['-vvv' => true]);

        $body =  [
            'email' => 'invalid@test.com',
            'password' => 'password'
        ];

        $this->json('POST', '/api/login', $body)
            ->assertStatus(401)->assertJson([
                "success" => false
            ]);
    }

    public function test_login_success()
    {
        Artisan::call('db:seed', ['-vvv' => true]);

        $body = [
            'email' => 'valid@test.com',
            'password' => 'password'
        ];

        $this->json('POST', '/api/login', $body, ['Accept' => 'application/json'])
            ->assertStatus(200)->assertJson([
                "success" => true
            ]);
    }

    public function test_register_success()
    {
        $body = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'c_password' => $this->password
        ];

        $this->json('POST', '/api/register', $body)
            ->assertStatus(200)->assertJson([
                "success" => true
            ]);
    }

    public function test_refresh_token()
    {
        Artisan::call('db:seed', ['-vvv' => true]);

        $response = $this->postJson('/api/login', [
            'email' => 'valid@test.com',
            'password' => 'password'
        ], ['Accept' => 'application/json']);

        $responseRefresh = $this->withHeaders([
            'Refreshtoken' => $response['refresh_token']
        ])->postJson('/api/refreshtoken');

        $responseRefresh->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }

    public function test_register_profile_success()
    {
        $body = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => '1987-10-10',
            'bloodtype' => 1,
            'phone' => $this->faker->phoneNumber,
            'gender' => 1,
            'country' => 1,
            'state' => 1,
        ];

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/user/profile', $body);

        $response->assertStatus(200);
    }
}
