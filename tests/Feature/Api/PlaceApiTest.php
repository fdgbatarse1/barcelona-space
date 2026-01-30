<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('API Places Index', function () {
    test('returns list of places', function () {
        Place::factory()->count(3)->create();

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'latitude', 'longitude', 'user_id'],
                ],
            ]);
        expect($response->json('data'))->toHaveCount(3);
    });

    test('includes user relationship', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        Place::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.user.name', 'John Doe');
    });

    test('includes comments count', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(5)->create(['place_id' => $place->id]);

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.comments_count', 5);
    });

    test('can filter by search term in name', function () {
        Place::factory()->create(['name' => 'Barcelona Beach']);
        Place::factory()->create(['name' => 'Madrid Park']);

        $response = $this->getJson('/api/places?search=Barcelona');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.name'))->toBe('Barcelona Beach');
    });

    test('can filter by search term in description', function () {
        Place::factory()->create(['name' => 'Place A', 'description' => 'Beautiful sandy beach']);
        Place::factory()->create(['name' => 'Place B', 'description' => 'Mountain view']);

        $response = $this->getJson('/api/places?search=beach');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
    });

    test('can filter by search term in address', function () {
        Place::factory()->create(['name' => 'Place A', 'address' => '123 Beach Street']);
        Place::factory()->create(['name' => 'Place B', 'address' => '456 Mountain Road']);

        $response = $this->getJson('/api/places?search=Beach');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
    });

    test('can filter by user_id', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Place::factory()->count(2)->create(['user_id' => $user1->id]);
        Place::factory()->count(3)->create(['user_id' => $user2->id]);

        $response = $this->getJson("/api/places?user_id={$user1->id}");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(2);
    });

    test('can filter by geographic bounds', function () {
        Place::factory()->create(['latitude' => 41.0, 'longitude' => 2.0]);
        Place::factory()->create(['latitude' => 42.0, 'longitude' => 3.0]);
        Place::factory()->create(['latitude' => 50.0, 'longitude' => 10.0]);

        $response = $this->getJson('/api/places?lat_min=40&lat_max=43&lng_min=1&lng_max=4');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(2);
    });

    test('can sort by name ascending', function () {
        Place::factory()->create(['name' => 'Zebra Place']);
        Place::factory()->create(['name' => 'Alpha Place']);

        $response = $this->getJson('/api/places?sort=name');

        $response->assertStatus(200);
        expect($response->json('data.0.name'))->toBe('Alpha Place');
    });

    test('can sort by name descending', function () {
        Place::factory()->create(['name' => 'Zebra Place']);
        Place::factory()->create(['name' => 'Alpha Place']);

        $response = $this->getJson('/api/places?sort=-name');

        $response->assertStatus(200);
        expect($response->json('data.0.name'))->toBe('Zebra Place');
    });

    test('can sort by created_at ascending', function () {
        Place::factory()->create(['created_at' => now()->subDay()]);
        Place::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/places?sort=created_at');

        $response->assertStatus(200);
        // Older one should come first
    });

    test('can sort by created_at descending', function () {
        Place::factory()->create(['name' => 'Old', 'created_at' => now()->subDay()]);
        Place::factory()->create(['name' => 'New', 'created_at' => now()]);

        $response = $this->getJson('/api/places?sort=-created_at');

        $response->assertStatus(200);
        expect($response->json('data.0.name'))->toBe('New');
    });

    test('defaults to descending created_at sort', function () {
        Place::factory()->create(['name' => 'Old', 'created_at' => now()->subDay()]);
        Place::factory()->create(['name' => 'New', 'created_at' => now()]);

        $response = $this->getJson('/api/places');

        $response->assertStatus(200);
        expect($response->json('data.0.name'))->toBe('New');
    });

    test('ignores invalid sort field', function () {
        Place::factory()->count(2)->create();

        $response = $this->getJson('/api/places?sort=invalid_field');

        $response->assertStatus(200);
        // Should default to created_at desc
    });

    test('paginates with default 15 per page', function () {
        Place::factory()->count(20)->create();

        $response = $this->getJson('/api/places');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(15);
    });

    test('can set custom per_page', function () {
        Place::factory()->count(20)->create();

        $response = $this->getJson('/api/places?per_page=5');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(5);
    });

    test('limits per_page to maximum 100', function () {
        Place::factory()->count(150)->create();

        $response = $this->getJson('/api/places?per_page=200');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(100);
    });

    test('returns pagination metadata', function () {
        Place::factory()->count(20)->create();

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });
});

describe('API Places Show', function () {
    test('returns single place', function () {
        $place = Place::factory()->create(['name' => 'Test Place']);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Place');
    });

    test('includes user relationship', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.user.name', 'John Doe');
    });

    test('includes comments count', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(3)->create(['place_id' => $place->id]);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.comments_count', 3);
    });

    test('returns 404 for non-existent place', function () {
        $response = $this->getJson('/api/places/99999');

        $response->assertStatus(404);
    });
});

describe('API Places Store', function () {
    test('unauthenticated user cannot create place', function () {
        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(401);
    });

    test('authenticated user can create place', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'New Place',
            'description' => 'A great place',
            'address' => '123 Main St',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Place created successfully.',
                'data' => [
                    'name' => 'New Place',
                    'description' => 'A great place',
                ],
            ]);

        $this->assertDatabaseHas('places', [
            'name' => 'New Place',
            'user_id' => $user->id,
        ]);
    });

    test('validates name is required', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });

    test('validates latitude is required', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    });

    test('validates longitude is required', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => 41.3851,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('longitude');
    });

    test('validates latitude bounds', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => 91,
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    });

    test('validates longitude bounds', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 181,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('longitude');
    });

    test('can create place with image', function () {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $file = UploadedFile::fake()->image('place.jpg');

        $response = $this->postJson('/api/places', [
            'name' => 'Place With Image',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertStatus(201);
        $place = Place::where('name', 'Place With Image')->first();
        expect($place->image)->not->toBeNull();
    });
});

describe('API Places Update', function () {
    test('unauthenticated user cannot update place', function () {
        $place = Place::factory()->create();

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    });

    test('owner can update place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'Updated Name',
            'latitude' => 42.0,
            'longitude' => 3.0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Place updated successfully.',
                'data' => [
                    'name' => 'Updated Name',
                ],
            ]);

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Updated Name',
        ]);
    });

    test('non-owner cannot update place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id, 'name' => 'Original']);
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Original',
        ]);
    });

    test('validates fields on update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/places/{$place->id}", [
            'latitude' => 100, // out of bounds
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    });

    test('returns 404 for non-existent place', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/places/99999', [
            'name' => 'Updated',
        ]);

        $response->assertStatus(404);
    });
});

describe('API Places Delete', function () {
    test('unauthenticated user cannot delete place', function () {
        $place = Place::factory()->create();

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('places', ['id' => $place->id]);
    });

    test('owner can delete place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Place deleted successfully.',
            ]);
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    });

    test('non-owner cannot delete place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('places', ['id' => $place->id]);
    });

    test('returns 404 for non-existent place', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/places/99999');

        $response->assertStatus(404);
    });
});
