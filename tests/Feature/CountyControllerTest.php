<?php

use App\Models\County;
use App\Models\PlaceName;
use App\Models\User;
use Database\Seeders\ZipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

pest()->use(RefreshDatabase::class);

test('it lists all counties', function () {
    County::factory()->create(['name' => 'Baranya']);
    County::factory()->create(['name' => 'Borsod-Abaúj-Zemplén']);

    $response = get('/api/counties');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'counties' => [
                    '*' => [
                        'id',
                        'name'
                    ],
                ],
            ],
        ])
        ->assertJsonFragment([
            'name' => 'Baranya',
            'name' => 'Borsod-Abaúj-Zemplén',
        ]);
    
});

test('it returns a specific county', function () {
    $county = County::factory()->create(['name' => 'Pest']);

    $response = get("/api/counties/{$county->id}");

    $response
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'county' => [
                    'id' => $county->id,
                    'name' => 'Pest',
                ],
            ],
        ])
        ->assertJsonFragment([
            'name' => 'Pest',
        ]);
});

test('it returns 404 for non-existing county', function () {
    $response = get('/api/counties/9999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});

test('it returns cities for a specific county', function () {
    $county = County::factory()->create(['name' => 'Pest']);
    PlaceName::factory()->create([
        'name' => 'Vác',
        'county_id' => $county->id,
    ]);

    $response = get("/api/counties/{$county->id}/place-names");

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'place_names' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ])
        ->assertJsonFragment([
            'name' => 'Vác',
        ]);
});

test('it returns 404 when fetching cities for non-existing county', function () {
    $response = get('/api/counties/9999/place-names');

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});

test('it returns a specific city in a county', function () {
    $county = County::factory()->create(['name' => 'Pest']);
    $placeName = PlaceName::factory()->create([
        'name' => 'Vác',
        'county_id' => $county->id,
    ]);

    $response = get("/api/counties/{$county->id}/place-names/{$placeName->id}");

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'place_name' => [
                    'id',
                    'name',
                ],
            ],
        ])
        ->assertJsonFragment([
            'name' => 'Vác',
        ]);
});

test('it returns 404 when fetching non-existing city in a county', function () {
    $county = County::factory()->create(['name' => 'Pest']);

    $response = get("/api/counties/{$county->id}/place-names/9999");

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'Place name not found in this county',
        ]);
});

test('it returns 404 when fetching a city in non-existing county', function () {
    $response = get('/api/counties/9999/place-names/1');

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});

test('it returns place name initials for a specific county', function () {
    $county = County::factory()->create(['name' => 'Pest']);
    PlaceName::factory()->create([
        'name' => 'Abony',
        'county_id' => $county->id,
    ]);
    PlaceName::factory()->create([
        'name' => 'Ábrahámhegy',
        'county_id' => $county->id,
    ]);
    PlaceName::factory()->create([
        'name' => 'Gödöllő',
        'county_id' => $county->id,
    ]);

    $response = get("/api/counties/{$county->id}/abc");

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'place_initials'
            ],
        ])
        ->assertJsonFragment([
            'place_initials' => ['A', 'Á', 'G'],
        ]);
});

test('it returns 404 when fetching place name initials for non-existing county', function () {
    $response = get('/api/counties/9999/abc');

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});

test('it creates a new county', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = postJson('/api/counties', [
        'name' => 'Győr-Moson-Sopron',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'county' => [
                    'name' => 'Győr-Moson-Sopron',
                ],
            ],
        ]);

    $this->assertDatabaseHas('counties', [
        'name' => 'Győr-Moson-Sopron',
    ]);
});

test('it validates county creation', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = postJson('/api/counties', [
        // 'name' is missing
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $response = postJson('/api/counties', [
        'name' => str_repeat('A', 256), // Exceeds max length
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
    
    postJson('/api/counties', [
        'name' => 'Győr-Moson-Sopron',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);
    $response = postJson('/api/counties', [
        'name' => 'Győr-Moson-Sopron', // Duplicate name
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('it updates an existing county', function () {
    $county = County::factory()->create(['name' => 'Fejér']);
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = \Pest\Laravel\putJson("/api/counties/{$county->id}", [
        'name' => 'Vas',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'county' => [
                    'id' => $county->id,
                    'name' => 'Vas',
                ],
            ],
        ]);

    $this->assertDatabaseHas('counties', [
        'id' => $county->id,
        'name' => 'Vas',
    ]);
});

test('it returns 404 when updating non-existing county', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = \Pest\Laravel\putJson('/api/counties/9999', [
        'name' => 'NonExistentCounty',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});

test('it deletes an existing county', function () {
    $county = County::factory()->create(['name' => 'Tolna']);
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = \Pest\Laravel\deleteJson("/api/counties/{$county->id}", [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(204);

    $this->assertDatabaseMissing('counties', [
        'id' => $county->id,
    ]);
});

test('it returns 404 when deleting non-existing county', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = \Pest\Laravel\deleteJson('/api/counties/9999', [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'County not found',
        ]);
});
