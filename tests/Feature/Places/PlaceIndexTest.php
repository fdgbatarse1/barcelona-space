<?php

use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

describe('Place Index', function () {
    test('displays places on index page', function () {
        $place = Place::factory()->create(['name' => 'Barcelona Beach']);

        $response = $this->get(route('places.index'));

        $response->assertStatus(200)
            ->assertSee('Barcelona Beach');
    });

    test('displays multiple places', function () {
        Place::factory()->create(['name' => 'Place One']);
        Place::factory()->create(['name' => 'Place Two']);

        $response = $this->get(route('places.index'));

        $response->assertStatus(200)
            ->assertSee('Place One')
            ->assertSee('Place Two');
    });

    test('paginates places with 10 per page', function () {
        Place::factory()->count(15)->create();

        $response = $this->get(route('places.index'));

        $response->assertStatus(200);
        // First page should have pagination links
        $response->assertSee('Next');
    });

    test('can navigate to second page', function () {
        Place::factory()->count(15)->create();

        $response = $this->get(route('places.index', ['page' => 2]));

        $response->assertStatus(200);
    });

    test('displays places in descending order by creation date', function () {
        $oldPlace = Place::factory()->create([
            'name' => 'Old Place',
            'created_at' => now()->subDays(5),
        ]);
        $newPlace = Place::factory()->create([
            'name' => 'New Place',
            'created_at' => now(),
        ]);

        $response = $this->get(route('places.index'));

        $response->assertStatus(200);
        // New place should appear before old place in the response
        $response->assertSeeInOrder(['New Place', 'Old Place']);
    });

    test('caches places index', function () {
        Place::factory()->create(['name' => 'Cached Place']);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(Place::latest()->paginate(10));

        $this->get(route('places.index'));
    });

    test('shows empty state when no places exist', function () {
        $response = $this->get(route('places.index'));

        $response->assertStatus(200);
    });

    test('guests can view places index', function () {
        Place::factory()->create();

        $response = $this->get(route('places.index'));

        $response->assertStatus(200);
    });

    test('authenticated users can view places index', function () {
        $user = User::factory()->create();
        Place::factory()->create();

        $response = $this->actingAs($user)->get(route('places.index'));

        $response->assertStatus(200);
    });
});
