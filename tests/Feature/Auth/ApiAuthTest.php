<?php

use App\Models\User;

test('api login returns token and user on valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $token = $response->json('token');
    expect($token)->not->toBeEmpty();

    $meResponse = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/auth/me');

    $meResponse->assertOk()->assertJsonFragment([
        'email' => $user->email,
    ]);
});

test('api login fails with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)->assertJson([
        'message' => 'Invalid credentials',
    ]);
});

test('api auth me requires authentication', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});
