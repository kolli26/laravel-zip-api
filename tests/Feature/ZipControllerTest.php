<?php

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('it returns all zip codes', function () {
    $response = get('/api/zip-codes');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'zip_codes' => [
                    '*' => [
                        'id',
                        'code',
                        'place_name' => [
                            'id',
                            'name',
                            'county' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
});

test('it returns a specific zip code', function () {
    $zipCode = \App\Models\ZipCode::factory()->create([
        'code' => '2600',
        'place_name_id' => \App\Models\PlaceName::factory()->create([
            'name' => 'Vác',
            'county_id' => \App\Models\County::factory()->create([
                'name' => 'Pest',
            ])->id,
        ])->id,
    ]);

    $zipCode->load('placeName', 'placeName.county');

    $response = get("/api/zip-codes/{$zipCode->id}");
    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'zip_code' => [
                    'id' => $zipCode->id,
                    'code' => '2600',
                    'place_name' => [
                        'id' => $zipCode->placeName->id,
                        'name' => 'Vác',
                        'county' => [
                            'id' => $zipCode->placeName->county->id,
                            'name' => 'Pest',
                        ],
                    ],
                ],
            ],
        ]);
});

test('it returns 404 for non-existing zip code', function () {
    $response = get('/api/zip-codes/999999');
    $response->assertStatus(404);
});

test('it creates a new zip code with new county and place name', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = postJson('/api/zip-codes', [
        'zip_code' => '1234',
        'place_name' => 'New Town',
        'county' => 'New County',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'zip_code' => [
                    'id',
                    'code',
                    'place_name' => [
                        'id',
                        'name',
                        'county' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);

    assertDatabaseHas('counties', ['name' => 'New County']);
    assertDatabaseHas('place_names', ['name' => 'New Town']);
    assertDatabaseHas('zip_codes', ['code' => '1234']);
});

test('it reuses existing county and place name when creating a new zip code', function () {
    $county = \App\Models\County::factory()->create(['name' => 'Existing County']);
    $placeName = \App\Models\PlaceName::factory()->create([
        'name' => 'Existing Town',
        'county_id' => $county->id,
    ]);

    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = postJson('/api/zip-codes', [
        'zip_code' => '5678',
        'place_name' => 'Existing Town',
        'county' => 'Existing County',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'zip_code' => [
                    'id',
                    'code',
                    'place_name' => [
                        'id',
                        'name',
                        'county' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);

    assertDatabaseHas('zip_codes', ['code' => '5678', 'place_name_id' => $placeName->id]);
});

test('it updates an existing zip code', function () {
    $zipCode = \App\Models\ZipCode::factory()->create([
        'code' => '9100',
        'place_name_id' => \App\Models\PlaceName::factory()->create([
            'name' => 'Old Town',
            'county_id' => \App\Models\County::factory()->create([
                'name' => 'Old County',
            ])->id,
        ])->id,
    ]);

    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = putJson("/api/zip-codes/{$zipCode->id}", [
        'zip_code' => '9101',
        'place_name' => 'Updated Town',
        'county_id' => $zipCode->placeName->county_id,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'zip_code' => [
                    'id',
                    'code',
                    'place_name' => [
                        'id',
                        'name',
                        'county' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);

    assertDatabaseHas('zip_codes', ['id' => $zipCode->id, 'code' => '9101']);
    assertDatabaseHas('place_names', ['name' => 'Updated Town']);
});

test('it returns 404 when updating non-existing zip code', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = putJson('/api/zip-codes/999999', [
        'zip_code' => '0000',
        'place_name' => 'Nonexistent Town',
        'county_id' => 1,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(404);
});

test('it returns validation errors when creating a zip code with invalid data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = postJson('/api/zip-codes', [
        'zip_code' => 'abcd',
        'place_name' => '',
        'county' => '',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['zip_code', 'place_name', 'county']);
});

test('it returns validation errors when updating a zip code with invalid data', function () {
    $zipCode = \App\Models\ZipCode::factory()->create();

    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = putJson("/api/zip-codes/{$zipCode->id}", [
        'zip_code' => '!!!!',
        'place_name' => '',
        'county_id' => 'not-a-number',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['zip_code', 'place_name', 'county_id']);
});

test('it deletes an existing zip code', function () {
    $zipCode = \App\Models\ZipCode::factory()->create();

    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->delete("/api/zip-codes/{$zipCode->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('zip_codes', ['id' => $zipCode->id]);
});

test('it returns 404 when deleting non-existing zip code', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->delete('/api/zip-codes/999999');

    $response->assertStatus(404);
});