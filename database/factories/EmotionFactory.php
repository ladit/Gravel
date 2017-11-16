<?php

use Faker\Generator as Faker;

$factory->define(App\Emotion::class, function (Faker $faker) {
    return [
        'content' => $faker->unique()->word
    ];
});
