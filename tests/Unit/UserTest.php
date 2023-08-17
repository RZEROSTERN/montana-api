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
        $this->password = $this->faker->password();
        $this->email = $this->faker->email();
    }

    public function test_login_error_with_data_ok()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@test.com',
            'password' => '12345678'
        ]);

        $response->assertStatus(401)->assertJson([
            "success" => false
        ]);
    }

    public function test_login_success()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'valid@test.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }

    public function test_register_success()
    {
        $response = $this->postJson('/api/register', [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'c_password' => $this->password
        ]);

        $response->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }

    public function test_refresh_token()
    {
        $user = User::where(['email' => 'valid@test.com'])->first();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $responseRefresh = $this->withHeaders([
            'Refreshtoken' => $response['refresh_token']
        ])->postJson('/api/refreshtoken');

        $responseRefresh->assertStatus(200)->assertJson([
            'success' => true
        ]);
    }
}
