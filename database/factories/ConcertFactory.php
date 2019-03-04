<?php

use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Concert::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'title' => 'The Fake Band Name',
    	'subtitle' => 'with The Fake Openers',
        'additional_information' => 'Some sample additional information.',    
        'date' => Carbon::parse('+4 weeks'),
        'venue' => 'The Fake Venue',
        'venue_address' => '123 Example Lane',
        'city' => 'Fakerville',
        'state' => 'FV',
        'zip' => '123456',
        'ticket_price' => 2000,
    	'ticket_quantity' => 5,
    ];
});

$factory->state(App\Models\Concert::class, 'published', [
    'published_at' => Carbon::parse('-1 week'),
]);

$factory->state(App\Models\Concert::class, 'unpublished', [
    'published_at' => null,
]);
