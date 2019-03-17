<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Invitation::class, function (Faker $faker) {
    return [
        'code' => 'TESTCODE1234',
        'email' => 'somebody@example.com',
    ];
});
