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

$factory->define(App\Models\Blog::class, function (Faker $faker) {
	$title = $faker->text(50);
    return [
        'user_id' => 1,
        'title' => $title,
        'slug' => str_slug($title),
        'image' => $faker->imageUrl($width = 640, $height = 480),
        'keywords' => str_slug($title, ', '),
        'description' => $faker->text(100),
        'body' => $faker->text(1000)
    ];
});
