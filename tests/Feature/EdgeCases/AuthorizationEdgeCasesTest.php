<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Resource Not Found Handling', function () {
    test('returns 404 for non-existent place via web', function () {
        $response = $this->get(route('places.show', 99999));

        $response->assertStatus(404);
    });

    test('returns 404 for non-existent place via API', function () {
        $response = $this->getJson('/api/places/99999');

        $response->assertStatus(404);
    });

    test('returns 404 for non-existent comment update via web', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('comments.update', 99999), [
            'text' => 'Updated',
        ]);

        $response->assertStatus(404);
    });

    test('returns 404 for non-existent comment delete via API', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/comments/99999');

        $response->assertStatus(404);
    });

    test('returns 404 when commenting on non-existent place', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', 99999), [
            'text' => 'Test comment',
        ]);

        $response->assertStatus(404);
    });
});

describe('Unauthenticated Access', function () {
    test('unauthenticated user cannot access place create form', function () {
        $response = $this->get(route('places.create'));

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated user cannot store place', function () {
        $response = $this->post(route('places.store'), [
            'name' => 'Test',
            'latitude' => 41.0,
            'longitude' => 2.0,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated user cannot edit place', function () {
        $place = Place::factory()->create();

        $response = $this->get(route('places.edit', $place));

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated user cannot update place', function () {
        $place = Place::factory()->create();

        $response = $this->put(route('places.update', $place), [
            'name' => 'Updated',
            'latitude' => 41.0,
            'longitude' => 2.0,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated user cannot delete place', function () {
        $place = Place::factory()->create();

        $response = $this->delete(route('places.destroy', $place));

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated user redirected when creating comment', function () {
        $place = Place::factory()->create();

        $response = $this->post(route('comments.store', $place), [
            'text' => 'Test',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('unauthenticated API request returns 401', function () {
        $response = $this->postJson('/api/places', [
            'name' => 'Test',
            'latitude' => 41.0,
            'longitude' => 2.0,
        ]);

        $response->assertStatus(401);
    });

    test('unauthenticated user cannot access API user endpoint', function () {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    });
});

describe('Authorization Ownership Checks', function () {
    test('user cannot edit another users place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('places.edit', $place));

        $response->assertStatus(403);
    });

    test('user cannot update another users place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->put(route('places.update', $place), [
            'name' => 'Hacked',
            'latitude' => 41.0,
            'longitude' => 2.0,
        ]);

        $response->assertStatus(403);
    });

    test('user cannot delete another users place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('places.destroy', $place));

        $response->assertStatus(403);
    });

    test('user cannot update another users comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->put(route('comments.update', $comment), [
            'text' => 'Hacked',
        ]);

        $response->assertStatus(403);
    });

    test('user cannot delete another users comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
    });

    test('place owner cannot delete others comments on their place', function () {
        $placeOwner = User::factory()->create();
        $commenter = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $placeOwner->id]);
        $comment = Comment::factory()->create([
            'user_id' => $commenter->id,
            'place_id' => $place->id,
        ]);

        $response = $this->actingAs($placeOwner)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
    });

    test('API user cannot update another users place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    });

    test('API user cannot delete another users place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(403);
    });

    test('API user cannot update another users comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => 'Hacked',
        ]);

        $response->assertStatus(403);
    });

    test('API user cannot delete another users comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    });
});

describe('Deleted Resource Access', function () {
    test('cannot access deleted place', function () {
        $place = Place::factory()->create();
        $placeId = $place->id;
        $place->delete();

        $response = $this->get(route('places.show', $placeId));

        $response->assertStatus(404);
    });

    test('cannot update deleted place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        $placeId = $place->id;
        $place->delete();

        $response = $this->actingAs($user)->put(route('places.update', $placeId), [
            'name' => 'Updated',
            'latitude' => 41.0,
            'longitude' => 2.0,
        ]);

        $response->assertStatus(404);
    });

    test('cannot delete already deleted place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        $placeId = $place->id;
        $place->delete();

        $response = $this->actingAs($user)->delete(route('places.destroy', $placeId));

        $response->assertStatus(404);
    });
});
