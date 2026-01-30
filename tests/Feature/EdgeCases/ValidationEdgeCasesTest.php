<?php

use App\Models\Place;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('Coordinate Boundary Validation', function () {
    test('latitude at minimum boundary -90 is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'South Pole',
            'latitude' => -90,
            'longitude' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'South Pole',
            'latitude' => -90,
        ]);
    });

    test('latitude at maximum boundary 90 is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'North Pole',
            'latitude' => 90,
            'longitude' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'North Pole',
            'latitude' => 90,
        ]);
    });

    test('latitude below minimum boundary fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Invalid Place',
            'latitude' => -90.1,
            'longitude' => 0,
        ]);

        $response->assertSessionHasErrors('latitude');
    });

    test('latitude above maximum boundary fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Invalid Place',
            'latitude' => 90.1,
            'longitude' => 0,
        ]);

        $response->assertSessionHasErrors('latitude');
    });

    test('longitude at minimum boundary -180 is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'West Edge',
            'latitude' => 0,
            'longitude' => -180,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'West Edge',
            'longitude' => -180,
        ]);
    });

    test('longitude at maximum boundary 180 is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'East Edge',
            'latitude' => 0,
            'longitude' => 180,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('places', [
            'name' => 'East Edge',
            'longitude' => 180,
        ]);
    });

    test('longitude below minimum boundary fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Invalid Place',
            'latitude' => 0,
            'longitude' => -180.1,
        ]);

        $response->assertSessionHasErrors('longitude');
    });

    test('longitude above maximum boundary fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Invalid Place',
            'latitude' => 0,
            'longitude' => 180.1,
        ]);

        $response->assertSessionHasErrors('longitude');
    });
});

describe('Image Upload Validation', function () {
    test('image file size exactly at 2MB limit is valid', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('place.jpg')->size(2048); // 2MB

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With Max Image',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
    });

    test('image file size above 2MB limit fails', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('large.jpg')->size(2049); // Just over 2MB

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With Large Image',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    });

    test('PDF file fails image validation', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With PDF',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    });

    test('text file fails image validation', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('file.txt', 100, 'text/plain');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With Text File',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('image');
    });

    test('JPEG image is valid', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('place.jpeg');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With JPEG',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
    });

    test('PNG image is valid', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('place.png');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With PNG',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
    });

    test('GIF image is valid', function () {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('place.gif');

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Place With GIF',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
            'image' => $file,
        ]);

        $response->assertRedirect();
    });
});

describe('String Length Validation', function () {
    test('name at exactly 255 characters is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => str_repeat('a', 255),
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
    });

    test('name at 256 characters fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => str_repeat('a', 256),
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('address at exactly 255 characters is valid', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'address' => str_repeat('a', 255),
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
    });

    test('comment text at exactly 1000 characters is valid', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => str_repeat('a', 1000),
        ]);

        $response->assertRedirect();
    });

    test('comment text at 1001 characters fails', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->post(route('comments.store', $place), [
            'text' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('text');
    });
});

describe('Empty and Null Value Handling', function () {
    test('empty name fails validation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => '',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('null name fails validation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => null,
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('whitespace only name fails validation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => '   ',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertSessionHasErrors('name');
    });

    test('empty description is accepted', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('places.store'), [
            'name' => 'Test Place',
            'description' => '',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response->assertRedirect();
    });
});

describe('API Validation Edge Cases', function () {
    test('API validates latitude boundary at -90', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'South Pole',
            'latitude' => -90,
            'longitude' => 0,
        ]);

        $response->assertStatus(201);
    });

    test('API rejects latitude beyond boundary', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Invalid',
            'latitude' => -91,
            'longitude' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    });

    test('API validates longitude boundary at 180', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Date Line',
            'latitude' => 0,
            'longitude' => 180,
        ]);

        $response->assertStatus(201);
    });

    test('API rejects longitude beyond boundary', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/places', [
            'name' => 'Invalid',
            'latitude' => 0,
            'longitude' => 181,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('longitude');
    });
});
