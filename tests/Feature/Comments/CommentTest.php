<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;

describe('Comment Create', function () {
    test('guests cannot create a comment', function () {
        $place = Place::factory()->create();

        $response = $this->post(route('comments.store', $place), [
            'text' => 'Test comment',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('authenticated users can create a comment', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => 'Great place!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'text' => 'Great place!',
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);
    });

    test('comment text is required', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => '',
        ]);

        $response->assertSessionHasErrors('text');
    });

    test('comment text cannot exceed 1000 characters', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('text');
    });

    test('comment text at exactly 1000 characters is valid', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => str_repeat('a', 1000),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'place_id' => $place->id,
        ]);
    });

    test('comment is associated with authenticated user', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => 'My comment',
        ]);

        $comment = Comment::where('text', 'My comment')->first();
        expect($comment->user_id)->toBe($user->id);
    });

    test('comment is associated with correct place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => 'Comment on this place',
        ]);

        $comment = Comment::where('text', 'Comment on this place')->first();
        expect($comment->place_id)->toBe($place->id);
    });

    test('sets success flash message after creating comment', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => 'Test comment',
        ]);

        $response->assertSessionHas('success');
    });

    test('returns 404 for non-existent place', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', 99999), [
            'text' => 'Test comment',
        ]);

        $response->assertStatus(404);
    });
});

describe('Comment Update', function () {
    test('guests cannot update a comment', function () {
        $comment = Comment::factory()->create(['text' => 'Original']);

        $response = $this->put(route('comments.update', $comment), [
            'text' => 'Updated',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('owner can update their comment', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'text' => 'Original text',
        ]);

        $response = $this->actingAs($user)->put(route('comments.update', $comment), [
            'text' => 'Updated text',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Updated text',
        ]);
    });

    test('non-owner cannot update a comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'text' => 'Original',
        ]);

        $response = $this->actingAs($otherUser)->put(route('comments.update', $comment), [
            'text' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Original',
        ]);
    });

    test('validates text on update', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('comments.update', $comment), [
            'text' => '',
        ]);

        $response->assertSessionHasErrors('text');
    });

    test('text cannot exceed 1000 characters on update', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('comments.update', $comment), [
            'text' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('text');
    });

    test('sets success flash message after update', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('comments.update', $comment), [
            'text' => 'Updated text',
        ]);

        $response->assertSessionHas('success');
    });

    test('returns 404 for non-existent comment', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('comments.update', 99999), [
            'text' => 'Updated',
        ]);

        $response->assertStatus(404);
    });
});

describe('Comment Delete', function () {
    test('guests cannot delete a comment', function () {
        $comment = Comment::factory()->create();

        $response = $this->delete(route('comments.destroy', $comment));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });

    test('owner can delete their comment', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    test('non-owner cannot delete a comment', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });

    test('sets success flash message after deletion', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('comments.destroy', $comment));

        $response->assertSessionHas('success');
    });

    test('returns 404 for non-existent comment', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('comments.destroy', 99999));

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

        $response = $this->actingAs($placeOwner)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });
});
