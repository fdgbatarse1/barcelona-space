<?php

use App\Models\Place;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Place Create', function () {
    test('guests cannot access create form', function () {
        $response = $this->get(route('places.create'));

        // Routes inside auth middleware redirect unauthenticated users to login
        $response->assertRedirect(route('login'));
    });

    test('authenticated users can access create form', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('places.create'));

        $response->assertStatus(200);
    });

    test('create form displays required fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('places.create'));

        $response->assertStatus(200)
            ->assertSee('name', false)
            ->assertSee('latitude', false)
            ->assertSee('longitude', false);
    });

    test('guests cannot store a place', function () {
        $response = $this->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('authenticated users can create a place', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Barcelona Beach',
            'description' => 'Beautiful beach',
            'address' => '123 Beach St',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'Barcelona Beach',
            'user_id' => $user->id,
        ]);
    });

    test('place is associated with authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('places.store'), [
            'name' => 'My Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $place = Place::where('name', 'My Place')->first();
        expect($place->user_id)->toBe($user->id);
    });

    test('name is required', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('name cannot exceed 255 characters', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => str_repeat('a', 256),
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('latitude is required', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('latitude');
    });

    test('longitude is required', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
        ]);

        $response->assertSessionHasErrors('longitude');
    });

    test('latitude must be numeric', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 'not-a-number',
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('latitude');
    });

    test('longitude must be numeric', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 'not-a-number',
        ]);

        $response->assertSessionHasErrors('longitude');
    });

    test('can upload image with place', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('place.jpg');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With Image',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
        $place = Place::where('name', 'Place With Image')->first();
        expect($place->image)->not->toBeNull();
    });

    test('image must be an image file', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    });

    test('image cannot exceed 2MB', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    });

    test('redirects to place show page after creation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'New Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $place = Place::where('name', 'New Place')->first();
        $response->assertRedirect(route('places.show', $place));
    });

    test('sets success flash message after creation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'New Place',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHas('status');
    });

    test('description is optional', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place Without Description',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'Place Without Description',
            'description' => null,
        ]);
    });

    test('address is optional', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place Without Address',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'Place Without Address',
            'address' => null,
        ]);
    });
});
