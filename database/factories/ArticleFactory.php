<?php

use Faker\Generator as Faker;

$factory->define(App\Article::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(),
        'author' => $faker->name(),
        'publish_time' => $faker->dateTime(),
        'url' => $faker->url(),
        'content' => $faker->paragraphs(5,true)
    ];
});
