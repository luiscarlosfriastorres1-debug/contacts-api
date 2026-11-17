
<?php

use Laravel\Sanctum\Sanctum;
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

it('login sin email o sin password devuelve 422', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => '',
        'password' => ''
    ]);

    $response->assertStatus(422);
});

it('login con email mal formado devuelve 422', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'no es un email',
        'password' => '12345678'
    ]);

    $response->assertStatus(422);
});



it('con token, responde 200 y el mensaje de sesion cerrada', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/logout');

    $response->assertOk()->assertJson(['message' => 'Sesión cerrada']);
});


it('despues de logout, el mismo token ya no sirve y devuelve 401', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $headers = ['Authorization' => 'Bearer ' . $token];

    $this->postJson('/api/v1/logout', [], $headers)->assertOk();

    $this->app['auth']->forgetGuards();

    $this->getJson('/api/v1/me', $headers)->assertUnauthorized();
});

it('sin token, devuelve 401', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

it('con token, devuelve el nombre y la fecha de creacion del usuario', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('nombre', $user->name)
        ->assertJsonPath('fecha_creacion', $user->created_at->toJSON());
});

