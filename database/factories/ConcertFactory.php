<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Concert::class, function (Faker $faker) {
    return [
        'title' => 'The Fake Band Name',
    	'subtitle' => 'with The Fake Openers',
    	'date' => Carbon::parse('December 14, 2019 8:00pm'),
    	'ticket_price' => 2000,
    	'venue' => 'The Fake Venue',
    	'venue_address' => '123 Example Lane',
    	'city' => 'Fakerville',
    	'state' => 'FV',
    	'zip' => '123456',
    	'additional_information' => 'Some sample additional information.',    
    ];
});
