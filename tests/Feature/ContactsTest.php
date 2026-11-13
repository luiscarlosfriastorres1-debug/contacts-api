<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('crea un contacto válido', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/contacts', [
        'name' => 'Ana Torres',
        'phone_number' => '3001234567',
    ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'Ana Torres')
        ->assertJsonPath('phone_number', '3001234567');

    $this->assertDatabaseHas('contacts', [
        'user_id' => $user->id,
        'name' => 'Ana Torres',
        'phone_number' => '3001234567',
        'email' => null,
    ]);
});

it('devuelve 404 para un contacto ajeno', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create([
        'name' => 'Contacto privado',
    ]);
    Sanctum::actingAs($visitor);

    $this->getJson('/api/v1/contacts/'.$contact->id)
        ->assertNotFound()
        ->assertJsonMissing(['name' => 'Contacto privado']);

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'user_id' => $owner->id,
        'name' => 'Contacto privado',
    ]);
});
