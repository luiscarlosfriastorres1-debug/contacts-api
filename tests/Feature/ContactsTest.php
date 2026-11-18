<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('crea un contacto válido', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/contactos', [
        'name' => 'Ana Torres',
        'phone_number' => '3001234567',
    ]);

    $response->assertCreated()
        ->assertJsonPath('contacto.name', 'Ana Torres')
        ->assertJsonPath('contacto.phone_number', '3001234567');

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

it('lista solo los contactos del usuario autenticado, no los de otros', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Contact::factory()->for($user)->create(['name' => 'Contacto 1']);
    Contact::factory()->for($user)->create(['name' => 'Contacto 2']);
    Contact::factory()->for($otherUser)->create(['name' => 'Contacto ajeno']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/contactos');

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['name' => 'Contacto 1'])
        ->assertJsonFragment(['name' => 'Contacto 2'])
        ->assertJsonMissing(['name' => 'Contacto ajeno']);
});

it('si el usuario no tiene contactos, devuelve un array vacío', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/contactos');

    $response->assertOk()
        ->assertExactJson([]);
});

it('no muestra los contactos eliminados, devuelve 404', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create(['name' => 'Contacto a eliminar']);
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/contactos/'.$contact->id)
        ->assertOk();

    $this->getJson('/api/v1/contactos/'.$contact->id)
        ->assertNotFound();
});

it('sin token, responde 401 al intentar acceder a contactos', function () {
    $this->getJson('/api/v1/contactos')
        ->assertUnauthorized();
});



it('ver un contacto propio devuelve 200 y los datos correctos', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create([
        'name' => 'Contacto propio',
        'phone_number' => '3001234567',
        'email' => 'ana.torres@example.com'
    ]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/contactos/'.$contact->id)
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Contacto propio',
            'phone_number' => '3001234567',
            'email' => 'ana.torres@example.com'
        ]);
});

it('ver un id que no responde a un contacto propio devuelve 404', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/contactos/999999')
        ->assertNotFound();
});

it('ver un contacto eliminado devuelve 404', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/contactos/'.$contact->id)
        ->assertOk();

    $this->getJson('/api/v1/contactos/'.$contact->id)
        ->assertNotFound();
});

it('actualizar un contacto propio devuelve 200 y los datos actualizados', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create([
        'name' => 'Contacto original',
        'phone_number' => '3001234567',
        'email' => 'ana.torres@example.com'
    ]);
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/contactos/'.$contact->id, [
        'name' => 'Contacto actualizado',
        'phone_number' => '3001234568',
        'email' => 'ana.torres.actualizada@example.com'
    ])
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Contacto actualizado',
            'phone_number' => '3001234568',
            'email' => 'ana.torres.actualizada@example.com'
        ]);
});

it('actualizar un campo por ejemplo el telefono, no borra los demas campos', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create([
        'name' => 'Contacto original',
        'phone_number' => '3001234567',
        'email' => 'ana.torres@example.com'
    ]);
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/contactos/'.$contact->id, [
        'phone_number' => '3001234568'
    ])
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Contacto original',
            'phone_number' => '3001234568',
            'email' => 'ana.torres@example.com'
        ]);
});



it('actualiza contacto propio y no permite actualizar contacto ajeno', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $contact = Contact::factory()->for($user)->create([
        'name' => 'Contacto original',
        'phone_number' => '3001234567',
        'email' => 'ana.torres@example.com'
    ]);
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/contactos/'.$contact->id, [
        'name' => 'Contacto actualizado',
        'phone_number' => '3001234568',
        'email' => 'ana.torres.actualizada@example.com'
    ])
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Contacto actualizado',
            'phone_number' => '3001234568',
            'email' => 'ana.torres.actualizada@example.com'
        ]);
});


