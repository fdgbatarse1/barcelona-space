<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]
        );

        Place::create([
            'user_id' => $user->getAttribute('id'),
            'name' => 'Sagrada Familia',
            'description' => 'The Sagrada Familia is a large unfinished Roman Catholic basilica in Barcelona, Catalonia, Spain. It is a UNESCO World Heritage Site.',
            'address' => 'Carrer de Mallorca, 401, 08013 Barcelona, Spain',
            'image' => 'places/sagrada-familia.jpg',
            'latitude' => 41.40381493322075,
            'longitude' => 2.1743021627656733,
        ]);

        Place::create([
            'user_id' => $user->getAttribute('id'),
            'name' => 'Park Güell',
            'description' => 'This palace was the home of industrialist Eusebi Guell and was Antonio Gaudi\'s first major building in the city.',
            'address' => 'Carrer Nou de la Rambla 3-5, 08001 Barcelona España',
            'image' => 'places/park-guell.jpg',
            'latitude' => 41.41492922378112,
            'longitude' => 2.152871543184104,
        ]);

        Place::create([
            'user_id' => $user->getAttribute('id'),
            'name' => 'Casa Batlló',
            'description' => "Welcome to Barcelona's magical house. A Gaudí masterpiece. A unique immersive experience. International Exhibition of the Year 2022. Children free up to 12 years old.",
            'address' => 'Passeig de Gràcia, 43, 08007 Barcelona Spain',
            'image' => 'places/casa-batllo.jpg',
            'latitude' => 41.391830055916074,
            'longitude' => 2.1649461581205536,
        ]);
    }
}
