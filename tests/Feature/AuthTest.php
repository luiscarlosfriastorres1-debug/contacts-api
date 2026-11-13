
<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('puede iniciar sesión con credenciales válidas', function () {
    $user = User::factory()->create(['password' => bcrypt('12345678')]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => '12345678'
    ]);

    $response->assertOk()->assertJsonStructure(['user','token']);
});

it('no puede iniciar sesión con credenciales inválidas', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'no@existe.com',
        'password' => '123'
    ]);

    $response->assertUnauthorized();
});
