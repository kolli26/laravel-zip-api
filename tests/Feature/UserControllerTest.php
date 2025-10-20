<?php

use function Pest\Laravel\post;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user
    \App\Models\User::factory()->create([
        'email' => 'test@test.dev',
        'password' => 'test123',
    ]);
});

test('login with valid credentials returns token', function () {
    $response = post('/api/users/login', [
        'email' => 'test@test.dev',
        'password' => 'test123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'token',
        ]
    ]);
});

test('login with invalid credentials returns error', function () {
    $response = post('/api/users/login', [
        'email' => 'invalid@test.dev',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
    $response->assertJson([
        'message' => 'Invalid credentials',
    ]);
});
