<?php

it('registers a buyer successfully', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Buyer',
        'email' => 'john.buyer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
    // Dump the response body to inspect JSON during the test run
    $response->dump();

    $response->assertCreated()
        ->assertJsonStructure(['message', 'user', 'token'])
        ->assertJsonPath('user.is_organizer', false);

    $this->assertDatabaseHas('users', [
        'email' => 'john.buyer@example.com',
        'is_organizer' => 0,
    ]);
});

it('registers an organizer successfully', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Organizer',
        'email' => 'jane.organizer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'is_organizer' => true,
    ]);
    // Dump the response body to inspect JSON during the test run
    $response->dump();

    $response->assertCreated()
        ->assertJsonStructure(['message', 'user', 'token'])
        ->assertJsonPath('user.is_organizer', true);

    $this->assertDatabaseHas('users', [
        'email' => 'jane.organizer@example.com',
        'is_organizer' => 1,
    ]);
});
