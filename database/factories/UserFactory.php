<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'account' => $faker->unique()->userName,
        'password' => $faker->md5,
        'safe_question' => $faker->sentence(),
        'safe_question_answer' => $faker->word,
        'access_token' => $faker->md5,
        'access_refresh_token' => $faker->md5,
        'access_token_expires_in' => $faker->dateTime($max = 'now', $timezone = date_default_timezone_get()),
        'qiniu_token' => $faker->md5,
        'qiniu_refresh_token' => $faker->md5,
        'qiniu_token_expires_in' => $faker->dateTime($max = 'now', $timezone = date_default_timezone_get()),
        'mail' => $faker->safeEmail,
        'phone_number' => $faker->numerify('1##########'),
        'nick_name' => $faker->name(),
        'avatar_url' => $faker->imageUrl(),
        'is_deleted' => $faker->numberBetween(0, 1),
        'is_blocked' => $faker->numberBetween(0, 1)
    ];
});
