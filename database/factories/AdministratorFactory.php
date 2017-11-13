<?php

use Faker\Generator as Faker;

$factory->define(App\Administrator::class, function (Faker $faker) {
    return [
        'account' => $faker->unique()->userName,
        'password' => $faker->md5,
        'access_token' => $faker->md5,
        'access_refresh_token' => $faker->md5,
        'access_token_expires_in' => $faker->dateTime($max = 'now', $timezone = date_default_timezone_get())
    ];
});
