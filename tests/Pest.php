<?php

use App\Models\Comment;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create and authenticate a user for web testing.
 */
function createAuthenticatedUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    test()->actingAs($user);

    return $user;
}

/**
 * Create and authenticate a user for API testing using Sanctum.
 */
function createApiUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    Sanctum::actingAs($user);

    return $user;
}

/**
 * Create a place with optional user.
 */
function createPlace(array $attributes = [], ?User $user = null): Place
{
    if ($user) {
        $attributes['user_id'] = $user->id;
    }

    return Place::factory()->create($attributes);
}

/**
 * Create a comment with optional user and place.
 */
function createComment(array $attributes = [], ?User $user = null, ?Place $place = null): Comment
{
    if ($user) {
        $attributes['user_id'] = $user->id;
    }

    if ($place) {
        $attributes['place_id'] = $place->id;
    }

    return Comment::factory()->create($attributes);
}
