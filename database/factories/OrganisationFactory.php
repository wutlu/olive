<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Organisation\Organisation::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'capacity' => 8,
        'start_date' => date('Y-m-d H:i:s'),
        'end_date' => date('Y-m-d H:i:s'),
        'user_id' => 1
    ];
});
