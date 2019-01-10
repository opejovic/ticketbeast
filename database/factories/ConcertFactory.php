<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Concert::class, function (Faker $faker) {
    return [
        'title' => 'The Fake Band Name',
    	'subtitle' => 'with The Fake Openers',
    	'date' => Carbon::parse('+4 weeks'),
    	'ticket_price' => 2000,
    	'venue' => 'The Fake Venue',
    	'venue_address' => '123 Example Lane',
    	'city' => 'Fakerville',
    	'state' => 'FV',
    	'zip' => '123456',
    	'additional_information' => 'Some sample additional information.',    
    ];
});

$factory->state(App\Models\Concert::class, 'published', [
    'published_at' => Carbon::parse('-1 week'),
]);

$factory->state(App\Models\Concert::class, 'unpublished', [
    'published_at' => null,
]);
