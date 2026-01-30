<?php

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

describe('CommentResource', function () {
    test('transforms comment to array with correct structure', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'text' => 'Test comment text',
        ]);

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array)->toHaveKeys([
            'id',
            'text',
            'place_id',
            'user_id',
            'created_at',
            'updated_at',
        ])
            ->and($array['id'])->toBe($comment->id)
            ->and($array['text'])->toBe('Test comment text')
            ->and($array['place_id'])->toBe($place->id)
            ->and($array['user_id'])->toBe($user->id);
    });

    test('includes user when relationship is loaded', function () {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $comment->load('user');

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array['user'])->not->toBeNull()
            ->and($array['user']['name'])->toBe('Jane Doe');
    });

    test('excludes user data when relationship is not loaded', function () {
        $comment = Comment::factory()->create();

        $resource = new CommentResource($comment);
        $response = $resource->toResponse(app('request'));
        $data = json_decode($response->content(), true);

        // whenLoaded returns MissingValue which is filtered from JSON output
        expect($data)->not->toHaveKey('user');
    });

    test('includes place when relationship is loaded', function () {
        $place = Place::factory()->create(['name' => 'Test Place']);
        $comment = Comment::factory()->create(['place_id' => $place->id]);
        $comment->load('place');

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array['place'])->not->toBeNull()
            ->and($array['place']['name'])->toBe('Test Place');
    });

    test('excludes place data when relationship is not loaded', function () {
        $comment = Comment::factory()->create();

        $resource = new CommentResource($comment);
        $response = $resource->toResponse(app('request'));
        $data = json_decode($response->content(), true);

        // whenLoaded returns MissingValue which is filtered from JSON output
        expect($data)->not->toHaveKey('place');
    });

    test('formats created_at as ISO string', function () {
        $comment = Comment::factory()->create();

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array['created_at'])->toBeString()
            ->and($array['created_at'])->toContain('T');
    });

    test('formats updated_at as ISO string', function () {
        $comment = Comment::factory()->create();

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array['updated_at'])->toBeString()
            ->and($array['updated_at'])->toContain('T');
    });

    test('can load both user and place relationships', function () {
        $user = User::factory()->create(['name' => 'John']);
        $place = Place::factory()->create(['name' => 'Barcelona']);
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);
        $comment->load(['user', 'place']);

        $resource = new CommentResource($comment);
        $array = $resource->toArray(new Request());

        expect($array['user']['name'])->toBe('John')
            ->and($array['place']['name'])->toBe('Barcelona');
    });
});
