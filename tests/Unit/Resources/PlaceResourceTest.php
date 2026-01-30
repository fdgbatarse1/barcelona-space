<?php

use App\Http\Resources\PlaceResource;
use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

describe('PlaceResource', function () {
    test('transforms place to array with correct structure', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Place',
            'description' => 'Test Description',
            'address' => 'Test Address',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array)->toHaveKeys([
            'id',
            'name',
            'description',
            'address',
            'image',
            'latitude',
            'longitude',
            'user_id',
            'created_at',
            'updated_at',
        ])
            ->and($array['id'])->toBe($place->id)
            ->and($array['name'])->toBe('Test Place')
            ->and($array['description'])->toBe('Test Description')
            ->and($array['address'])->toBe('Test Address')
            ->and($array['latitude'])->toBe(41.3851)
            ->and($array['longitude'])->toBe(2.1734)
            ->and($array['user_id'])->toBe($user->id);
    });

    test('returns null for image when no image exists', function () {
        $place = Place::factory()->create(['image' => null]);

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['image'])->toBeNull();
    });

    test('returns full URL for image when image exists', function () {
        $place = Place::factory()->withImage()->create();

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['image'])->toContain('storage/places/test-image.jpg');
    });

    test('casts latitude and longitude to float', function () {
        $place = Place::factory()->create([
            'latitude' => '41.3851',
            'longitude' => '2.1734',
        ]);

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['latitude'])->toBeFloat()
            ->and($array['longitude'])->toBeFloat();
    });

    test('includes user when relationship is loaded', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $place = Place::factory()->create(['user_id' => $user->id]);
        $place->load('user');

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['user'])->not->toBeNull()
            ->and($array['user']['name'])->toBe('John Doe');
    });

    test('excludes user data when relationship is not loaded', function () {
        $place = Place::factory()->create();

        $resource = new PlaceResource($place);
        $response = $resource->toResponse(app('request'));
        $data = json_decode($response->content(), true);

        // whenLoaded returns MissingValue which is filtered from JSON output
        expect($data)->not->toHaveKey('user');
    });

    test('includes comments when relationship is loaded', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(2)->create(['place_id' => $place->id]);
        $place->load('comments');

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['comments'])->toHaveCount(2);
    });

    test('includes comments_count when counted', function () {
        $place = Place::factory()->create();
        Comment::factory()->count(3)->create(['place_id' => $place->id]);
        $place->loadCount('comments');

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['comments_count'])->toBe(3);
    });

    test('formats created_at as ISO string', function () {
        $place = Place::factory()->create();

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['created_at'])->toBeString()
            ->and($array['created_at'])->toContain('T'); // ISO format includes T separator
    });

    test('formats updated_at as ISO string', function () {
        $place = Place::factory()->create();

        $resource = new PlaceResource($place);
        $array = $resource->toArray(new Request());

        expect($array['updated_at'])->toBeString()
            ->and($array['updated_at'])->toContain('T');
    });
});
