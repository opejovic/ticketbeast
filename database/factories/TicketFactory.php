<?php

use App\Models\Concert;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
        	return factory(Concert::class)->create()->id;
        }
    ];
});

$factory->state(App\Models\Ticket::class, 'reserved', [
		'reserved_at' => Carbon::parse('-1 hour'),
    ]
);
