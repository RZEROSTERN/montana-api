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
    public $deviceId;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();

        $this->name = $this->faker->name();
        $this->password = 'password';
        $this->email = 'valid@test.com';
        $this->deviceId = $this->faker->uuid();

        Artisan::call('migrate:fresh', ['-vvv' => true]);
    }

    public function test_login_error_with_data_ok()
    {
        Artisan::call('db:seed', ['-vvv' => true]);

        $body =  [
            'email' => 'invalid@test.com',
            'password' => 'password',
            'device_id' => $this->deviceId
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
            'password' => 'password',
            'device_id' => $this->deviceId
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
            'c_password' => $this->password,
            'device_id' => $this->deviceId
        ];

        $this->json('POST', '/api/register', $body)
            ->assertStatus(200)->assertJson([
                "success" => true
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
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/user/profile', $body);

        $response->assertStatus(200);
    }

    public function test_register_profile_validation_failed()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/user/profile', []);

        $response->assertStatus(400);
    }

    public function test_obtain_profile_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/user/profile');

        $response->assertStatus(404);
    }

    public function test_register_profile_and_obtain()
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
        $token = $user->createToken('TestToken')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/user/profile', $body);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/user/profile');

        $response->assertStatus(200);
    }
}
