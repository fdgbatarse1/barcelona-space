<?php

use App\Models\Place;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Place Edit', function () {
    test('guests cannot access edit form', function () {
        $place = Place::factory()->create();

        $response = $this->get(route('places.edit', $place));

        $response->assertRedirect(route('login'));
    });

    test('owner can access edit form', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('places.edit', $place));

        $response->assertStatus(200);
    });

    test('non-owner cannot access edit form', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('places.edit', $place));

        $response->assertStatus(403);
    });

    test('edit form displays current place data', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $response = $this->actingAs($user)->get(route('places.edit', $place));

        $response->assertStatus(200)
            ->assertSee('Original Name')
            ->assertSee('Original Description');
    });

    test('guests cannot update a place', function () {
        $place = Place::factory()->create();

        $response = $this->put(route('places.update', $place), [
            'name' => 'Updated Name',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('owner can update a place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    });

    test('non-owner cannot update a place', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $place = Place::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($otherUser)->put(route('places.update', $place), [
            'name' => 'Hacked Name',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Original Name',
        ]);
    });

    test('validates name on update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => '',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('validates latitude on update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Test Place',
            'latitude' => 'invalid',
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('latitude');
    });

    test('validates longitude on update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 'invalid',
        ]);

        $response->assertSessionHasErrors('longitude');
    });

    test('can update place image', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);
        $file = UploadedFile::fake()->image('new-image.jpg');

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Updated Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
        $place->refresh();
        expect($place->image)->not->toBeNull();
    });

    test('redirects to place show page after update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Updated Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect(route('places.show', $place));
    });

    test('sets success flash message after update', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Updated Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHas('status');
    });

    test('can update only specific fields', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original',
            'description' => 'Original description',
        ]);

        $this->actingAs($user)->put(route('places.update', $place), [
            'name' => 'Updated Name',
            'description' => 'New description',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $place->refresh();
        expect($place->name)->toBe('Updated Name')
            ->and($place->description)->toBe('New description');
    });
});
