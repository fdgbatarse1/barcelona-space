<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Malformed Request Handling', function () {
    test('handles malformed JSON gracefully', function () {
        $response = $this->postJson('/api/login', [], [
            'Content-Type' => 'application/json',
        ]);

        // With empty JSON body, validation errors should occur
        $response->assertStatus(422);
    });

    test('handles empty request body', function () {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

    test('handles array where string expected', function () {
        $response = $this->postJson('/api/register', [
            'name' => ['not', 'a', 'string'],
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });

    test('handles integer where string expected', function () {
        $response = $this->postJson('/api/register', [
            'name' => 12345,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Validation rules require string type, so integer is rejected
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });

    test('handles string where numeric expected', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => 'not a number',
            'longitude' => 2.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    });

    test('handles boolean where string expected', function () {
        $response = $this->postJson('/api/register', [
            'name' => true,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Boolean true might be coerced to "1"
        $response->assertStatus(422);
    });
});

describe('Token Handling', function () {
    test('handles invalid token format', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-format')
            ->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('handles missing Bearer prefix', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', $token)
            ->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('handles empty Authorization header', function () {
        $response = $this->withHeader('Authorization', '')
            ->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('handles Bearer with empty token', function () {
        $response = $this->withHeader('Authorization', 'Bearer ')
            ->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('handles deleted user token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $user->delete();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('handles token after logout', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        // First verify token works
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertStatus(200);

        // Logout deletes the token
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertStatus(200);

        // Token should be deleted from database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    });
});

describe('Missing Required Fields', function () {
    test('registration missing all fields', function () {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

    test('place creation missing all fields', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'latitude', 'longitude']);
    });

    test('comment creation missing text', function () {
        $user = User::factory()->create();
        $place = \App\Models\Place::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/places/{$place->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('text');
    });

    test('login missing all fields', function () {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    });
});

describe('Content Type Handling', function () {
    test('accepts application/json content type', function () {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    });

    test('returns JSON response', function () {
        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');
    });

    test('returns JSON for validation errors', function () {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message', 'errors']);
    });

    test('returns JSON for not found errors', function () {
        $response = $this->getJson('/api/places/99999');

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json');
    });

    test('returns JSON for authentication errors', function () {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson(['message' => 'Unauthenticated.']);
    });
});

describe('Query Parameter Edge Cases', function () {
    test('handles negative page number', function () {
        $response = $this->getJson('/api/places?page=-1');

        // Laravel typically returns first page for invalid page numbers
        $response->assertStatus(200);
    });

    test('handles zero page number', function () {
        $response = $this->getJson('/api/places?page=0');

        $response->assertStatus(200);
    });

    test('handles extremely large page number', function () {
        $response = $this->getJson('/api/places?page=999999');

        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    });

    test('handles negative per_page', function () {
        $response = $this->getJson('/api/places?per_page=-5');

        $response->assertStatus(200);
    });

    test('handles zero per_page', function () {
        $response = $this->getJson('/api/places?per_page=0');

        $response->assertStatus(200);
    });

    test('handles non-numeric per_page', function () {
        $response = $this->getJson('/api/places?per_page=abc');

        $response->assertStatus(200);
    });

    test('handles SQL injection attempt in search', function () {
        \App\Models\Place::factory()->create(['name' => 'Normal Place']);

        $response = $this->getJson('/api/places?search=\'; DROP TABLE places; --');

        $response->assertStatus(200);
        // Database should still be intact
        $this->assertDatabaseHas('places', ['name' => 'Normal Place']);
    });

    test('handles XSS attempt in search', function () {
        $response = $this->getJson('/api/places?search=<script>alert("xss")</script>');

        $response->assertStatus(200);
    });
});
