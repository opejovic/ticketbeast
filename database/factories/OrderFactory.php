<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Order::class, function (Faker $faker) {
    return [
        'amount' => 3250,
        'email' => 'somebody@example.com',
    ];
});
