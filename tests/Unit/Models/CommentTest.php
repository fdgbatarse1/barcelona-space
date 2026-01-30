<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Comment Model', function () {
    test('can be created using factory', function () {
        $comment = Comment::factory()->create();

        expect($comment)->toBeInstanceOf(Comment::class)
            ->and($comment->id)->toBeInt()
            ->and($comment->text)->toBeString();
    });

    test('has fillable attributes', function () {
        $comment = new Comment();

        expect($comment->getFillable())->toContain(
            'text',
            'user_id',
            'place_id'
        );
    });

    test('belongs to a user', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        expect($comment->user)->toBeInstanceOf(User::class)
            ->and($comment->user->id)->toBe($user->id);
    });

    test('belongs to a place', function () {
        $place = Place::factory()->create();
        $comment = Comment::factory()->create(['place_id' => $place->id]);

        expect($comment->place)->toBeInstanceOf(Place::class)
            ->and($comment->place->id)->toBe($place->id);
    });

    test('can be created for a specific user and place', function () {
        $user = User::factory()->create();
        $place = Place::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'text' => 'Test comment',
        ]);

        expect($comment->user_id)->toBe($user->id)
            ->and($comment->place_id)->toBe($place->id)
            ->and($comment->text)->toBe('Test comment');
    });

    test('deleting comment does not delete user', function () {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $comment->delete();

        expect(User::find($user->id))->not->toBeNull();
    });

    test('deleting comment does not delete place', function () {
        $place = Place::factory()->create();
        $comment = Comment::factory()->create(['place_id' => $place->id]);

        $comment->delete();

        expect(Place::find($place->id))->not->toBeNull();
    });

    test('can eager load user relationship', function () {
        $comment = Comment::factory()->create();
        $loadedComment = Comment::with('user')->find($comment->id);

        expect($loadedComment->relationLoaded('user'))->toBeTrue();
    });

    test('can eager load place relationship', function () {
        $comment = Comment::factory()->create();
        $loadedComment = Comment::with('place')->find($comment->id);

        expect($loadedComment->relationLoaded('place'))->toBeTrue();
    });

    test('can eager load both user and place relationships', function () {
        $comment = Comment::factory()->create();
        $loadedComment = Comment::with(['user', 'place'])->find($comment->id);

        expect($loadedComment->relationLoaded('user'))->toBeTrue()
            ->and($loadedComment->relationLoaded('place'))->toBeTrue();
    });
});
