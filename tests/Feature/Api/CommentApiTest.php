<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('API Comments Index', function () {
    test('returns list of comments for a place', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(3)->create(['place_id' => $place->id]);

        $response = $this->getJson("/api/places/{$place->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'text', 'user_id', 'place_id', 'created_at', 'updated_at'],
                ],
            ]);
        expect($response->json('data'))->toHaveCount(3);
    });

    test('includes user relationship', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $place = Place::factory()->create();
        Comment::factory()->create(['place_id' => $place->id, 'user_id' => $user->id]);

        $response = $this->getJson("/api/places/{$place->id}/comments");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.user.name', 'John Doe');
    });

    test('returns only comments for specified place', function () {
        $place1 = Place::factory()->create();
        $place2 = Place::factory()->create();
        Comment::factory()->count(2)->create(['place_id' => $place1->id]);
        Comment::factory()->count(3)->create(['place_id' => $place2->id]);

        $response = $this->getJson("/api/places/{$place1->id}/comments");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(2);
    });

    test('can sort by created_at ascending', function () {
        $place = Place::factory()->create();
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'Old', 'created_at' => now()->subDay()]);
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'New', 'created_at' => now()]);

        $response = $this->getJson("/api/places/{$place->id}/comments?sort=created_at");

        $response->assertStatus(200);
        expect($response->json('data.0.text'))->toBe('Old');
    });

    test('can sort by created_at descending', function () {
        $place = Place::factory()->create();
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'Old', 'created_at' => now()->subDay()]);
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'New', 'created_at' => now()]);

        $response = $this->getJson("/api/places/{$place->id}/comments?sort=-created_at");

        $response->assertStatus(200);
        expect($response->json('data.0.text'))->toBe('New');
    });

    test('defaults to descending created_at sort', function () {
        $place = Place::factory()->create();
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'Old', 'created_at' => now()->subDay()]);
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'New', 'created_at' => now()]);

        $response = $this->getJson("/api/places/{$place->id}/comments");

        $response->assertStatus(200);
        expect($response->json('data.0.text'))->toBe('New');
    });

    test('paginates with default 15 per page', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(20)->create(['place_id' => $place->id]);

        $response = $this->getJson("/api/places/{$place->id}/comments");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(15);
    });

    test('can set custom per_page', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(20)->create(['place_id' => $place->id]);

        $response = $this->getJson("/api/places/{$place->id}/comments?per_page=5");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(5);
    });

    test('limits per_page to maximum 100', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(150)->create(['place_id' => $place->id]);

        $response = $this->getJson("/api/places/{$place->id}/comments?per_page=200");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(100);
    });

    test('returns 404 for non-existent place', function () {
        $response = $this->getJson('/api/places/99999/comments');

        $response->assertStatus(404);
    });

    test('returns empty array for place with no comments', function () {
        $place = Place::factory()->create();

        $response = $this->getJson("/api/places/{$place->id}/comments");

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(0);
    });
});

describe('API Comments Store', function () {
    test('unauthenticated user cannot create comment', function () {
        $place = Place::factory()->create();

        $response = $this->postJson("/api/places/{$place->id}/comments", [
            'text' => 'Test comment',
        ]);

        $response->assertStatus(401);
    });

    test('authenticated user can create comment', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/places/{$place->id}/comments", [
            'text' => 'Great place!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Comment created successfully.',
                'data' => [
                    'text' => 'Great place!',
                    'user_id' => $user->id,
                    'place_id' => $place->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'text' => 'Great place!',
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);
    });

    test('validates text is required', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/places/{$place->id}/comments", [
            'text' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('text');
    });

    test('validates text max length', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/places/{$place->id}/comments", [
            'text' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('text');
    });

    test('includes user in response', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $place = Place::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/places/{$place->id}/comments", [
            'text' => 'Test comment',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.name', 'John Doe');
    });

    test('returns 404 for non-existent place', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places/99999/comments', [
            'text' => 'Test comment',
        ]);

        $response->assertStatus(404);
    });
});

describe('API Comments Update', function () {
    test('unauthenticated user cannot update comment', function () {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertStatus(401);
    });

    test('owner can update comment', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'text' => 'Original']);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment updated successfully.',
                'data' => [
                    'text' => 'Updated text',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Updated text',
        ]);
    });

    test('non-owner cannot update comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id, 'text' => 'Original']);
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Original',
        ]);
    });

    test('validates text is required', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('text');
    });

    test('validates text max length on update', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('text');
    });

    test('returns 404 for non-existent comment', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/comments/99999', [
            'text' => 'Updated',
        ]);

        $response->assertStatus(404);
    });
});

describe('API Comments Delete', function () {
    test('unauthenticated user cannot delete comment', function () {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });

    test('owner can delete comment', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment deleted successfully.',
            ]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    test('non-owner cannot delete comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });

    test('returns 404 for non-existent comment', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/comments/99999');

        $response->assertStatus(404);
    });

    test('place owner cannot delete others comments', function () {
        $placeOwner = User::factory()->create();
        $commentOwner = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $placeOwner->id]);
        $comment = Comment::factory()->create([
            'user_id' => $commentOwner->id,
            'place_id' => $place->id,
        ]);
        Sanctum::actingAs($placeOwner);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });
});
