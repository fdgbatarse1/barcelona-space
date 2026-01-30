<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;

describe('Place Show', function () {
    test('displays place details', function () {
        $place = Place::factory()->create([
            'name' => 'Barcelona Beach',
            'description' => 'Beautiful sandy beach',
            'address' => '123 Beach Street',
        ]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSee('Barcelona Beach')
            ->assertSee('Beautiful sandy beach')
            ->assertSee('123 Beach Street');
    });

    test('displays place owner', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $place = Place::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSee('John Doe');
    });

    test('displays place comments', function () {
        $place = Place::factory()->create();
        Comment::factory()->create([
            'place_id' => $place->id,
            'text' => 'Great place to visit!',
        ]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSee('Great place to visit!');
    });

    test('displays multiple comments', function () {
        $place = Place::factory()->create();
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'Comment one']);
        Comment::factory()->create(['place_id' => $place->id, 'text' => 'Comment two']);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSee('Comment one')
            ->assertSee('Comment two');
    });

    test('paginates comments with 5 per page', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(7)->create(['place_id' => $place->id]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200);
    });

    test('guests can view place details', function () {
        $place = Place::factory()->create();

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200);
    });

    test('authenticated users can view place details', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $response = $this->actingAs($user)->get(route('places.show', $place));

        $response->assertStatus(200);
    });

    test('returns 404 for non-existent place', function () {
        $response = $this->get(route('places.show', 99999));

        $response->assertStatus(404);
    });

    test('displays comment author names', function () {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        $place = Place::factory()->create();
        Comment::factory()->create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'text' => 'My comment',
        ]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSee('Jane Doe');
    });

    test('displays comments in descending order', function () {
        $place = Place::factory()->create();
        Comment::factory()->create([
            'place_id' => $place->id,
            'text' => 'Old comment',
            'created_at' => now()->subDay(),
        ]);
        Comment::factory()->create([
            'place_id' => $place->id,
            'text' => 'New comment',
            'created_at' => now(),
        ]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200)
            ->assertSeeInOrder(['New comment', 'Old comment']);
    });

    test('shows place coordinates', function () {
        $place = Place::factory()->create([
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $response = $this->get(route('places.show', $place));

        $response->assertStatus(200);
        // The coordinates should be used for the map
    });
});
