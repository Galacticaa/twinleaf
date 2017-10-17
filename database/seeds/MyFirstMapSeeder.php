<?php

use Illuminate\Database\Seeder;
use Twinleaf\Account;
use Twinleaf\Map;
use Twinleaf\MapArea;

class MyFirstMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Map::create([
            'name' => 'My First Map',
            'code' => 'my-first-map',
            'url' => 'http://localhost:8001',
            'location' => '',
            'db_name' => 'myfirstmap',
            'db_user' => 'root',
            'db_pass' => 'root',
        ]);

        MapArea::create([
            'name' => 'Santa Monica',
            'slug' => 'santa-monica',
            'map_id' => 1,
            'location' => '35.31233, 138.5892',
        ]);

        for ($i = 0; $i < 25; $i++) {
            $faker = \Faker\Factory::create();

            $username = $faker->bothify('walterWhite#?##?');

            Account::create([
                'map_area_id' => 1,
                'username' => $username,
                'password' => 'Hunter01',
                'email' => $username.'@example.com',
                'country' => 'GB',
                'birthday' => $faker->date('Y-m-d', '-18 years'),
                'is_blind' => $faker->randomElement([null, true, false]),
                'is_banned' => $faker->randomElement([null, true, false]),
            ]);
        }
    }
}
