<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

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
        Artisan::call('db:seed', ['-vvv' => true]);
    }

    public function test_login_error_with_data_ok()
    {
        $body =  [
            'email' => 'invalid@test.com',
            'password' => 'password'
        ];

        $this->json('POST', '/api/login', $body)
            ->assertStatus(401)->assertJson([
                "success" => false
            ]);
    }

    public function test_oauth_login_success()
    {
        $client = new Client();

        $response = $client->post(env('APP_URL') . 'oauth/token', [
            RequestOptions::JSON => [
                'grant_type' => 'password',
                'client_id' => "5",
                'client_secret' => 'T7gtfKg2YvAXaPmFimlY68ktHs5lGxWoDiYbDIvX',
                'username' => 'valid@test.com',
                'password' => 'password',
                'scope' => '*',
            ]
        ]);

        var_dump($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());
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
}
