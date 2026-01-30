<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Place Model', function () {
    test('can be created using factory', function () {
        $place = Place::factory()->create();

        expect($place)->toBeInstanceOf(Place::class)
            ->and($place->id)->toBeInt()
            ->and($place->name)->toBeString()
            ->and($place->latitude)->toBeNumeric()
            ->and($place->longitude)->toBeNumeric();
    });

    test('has fillable attributes', function () {
        $place = new Place();

        expect($place->getFillable())->toContain(
            'user_id',
            'name',
            'description',
            'address',
            'image',
            'latitude',
            'longitude'
        );
    });

    test('belongs to a user', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        expect($place->user)->toBeInstanceOf(User::class)
            ->and($place->user->id)->toBe($user->id);
    });

    test('has many comments', function () {
        $place = Place::factory()->create();
        $comments = Comment::factory()->count(3)->create(['place_id' => $place->id]);

        expect($place->comments)->toHaveCount(3)
            ->and($place->comments->first())->toBeInstanceOf(Comment::class);
    });

    test('can create place with specific coordinates', function () {
        $place = Place::factory()->withCoordinates(41.3851, 2.1734)->create();

        expect($place->latitude)->toBe(41.3851)
            ->and($place->longitude)->toBe(2.1734);
    });

    test('can create place with image state', function () {
        $place = Place::factory()->withImage()->create();

        expect($place->image)->toBe('places/test-image.jpg');
    });

    test('place without image has null image', function () {
        $place = Place::factory()->create();

        expect($place->image)->toBeNull();
    });

    test('latitude is stored as numeric value', function () {
        $place = Place::factory()->create(['latitude' => 41.3851]);

        expect((float) $place->latitude)->toBe(41.3851);
    });

    test('longitude is stored as numeric value', function () {
        $place = Place::factory()->create(['longitude' => 2.1734]);

        expect((float) $place->longitude)->toBe(2.1734);
    });

    test('deleting place does not delete user', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create(['user_id' => $user->id]);

        $place->delete();

        expect(User::find($user->id))->not->toBeNull();
    });

    test('can eager load user relationship', function () {
        $place = Place::factory()->create();
        $loadedPlace = Place::with('user')->find($place->id);

        expect($loadedPlace->relationLoaded('user'))->toBeTrue();
    });

    test('can eager load comments relationship', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(2)->create(['place_id' => $place->id]);
        $loadedPlace = Place::with('comments')->find($place->id);

        expect($loadedPlace->relationLoaded('comments'))->toBeTrue()
            ->and($loadedPlace->comments)->toHaveCount(2);
    });
});
