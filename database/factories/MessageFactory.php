<?php

use Faker\Generator as Faker;

$factory->define(App\Message::class, function (Faker $faker) {
    return [
        'note_id' => $faker->randomNumber(),
        'user_id' => $faker->randomNumber(),
        'is_upvoted' => $faker->numberBetween(0, 1),
        'is_reported' => $faker->numberBetween(0, 1),
        'is_sent' => $faker->numberBetween(0, 1)
    ];
});
