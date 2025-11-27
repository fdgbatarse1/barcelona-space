<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        $places = \App\Models\Place::all();

        if ($users->isEmpty() || $places->isEmpty()) {
            return;
        }

        foreach ($places as $place) {
            \App\Models\Comment::factory(rand(3, 5))->create([
                'place_id' => $place->id,
                'user_id' => $users->random()->id,
            ]);
        }
    }
}
