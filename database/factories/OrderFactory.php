<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Order::class, function (Faker $faker) {
    return [
        'confirmation_number' => 'ORDERCONFIRMATION1234',
        'amount' => 3250,
        'email' => 'somebody@example.com',
        'card_last_four' => 1234,
    ];
});
