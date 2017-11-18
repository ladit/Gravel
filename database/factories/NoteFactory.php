<?php

use Faker\Generator as Faker;

$factory->define(App\Note::class, function (Faker $faker) {
    return [
        'user_id' => $faker->randomNumber(),
        'url' => $faker->url(),
        'content' => $faker->paragraphs(5,true),
        'is_shared' => $faker->numberBetween(0, 1),
        'is_deleted' => $faker->numberBetween(0, 1),
        'is_blocked' => $faker->numberBetween(0, 1)
    ];
});
