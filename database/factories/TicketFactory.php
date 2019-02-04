<?php

use App\Models\Concert;
use Faker\Generator as Faker;

$factory->define(App\Models\Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
        	return factory(Concert::class)->create()->id;
        }
    ];
});
