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

$factory->define(App\Models\Forum\Message::class, function (Faker $faker) {
    return [
        'subject' => $faker->text(50),
        'body' => implode(PHP_EOL, [
        	$faker->text(200),
        	$faker->text(200),
        	$faker->text(100)
        ]),
        'category_id' => 1,
        'user_id' => 1,
        'hit' => 0,
        'closed' => false,
        'static' => false
    ];
});
