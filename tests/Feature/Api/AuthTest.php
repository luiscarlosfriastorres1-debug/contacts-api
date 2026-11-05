<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_ok()
    {
        $user = User::factory()->create(['password' => bcrypt('12345678')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => '12345678'
        ]);

        $response->assertOk()->assertJsonStructure(['user','token']);
    }

    public function test_login_fail()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'no@existe.com',
            'password' => '123'
        ]);

        $response->assertUnauthorized();
    }

}