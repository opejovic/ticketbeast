<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function user_can_view_concert_listing()
	{
		$this->withoutExceptionHandling();

	    $concert = factory(Concert::class)->create([
	    	'title' => 'The Red Chord',
	    	'subtitle' => 'with Animosity and Lethargy',
	    	'date' => Carbon::parse('December 13, 2019 8:00pm'),
	    	'ticket_price' => 3250,
	    	'venue' => 'The Mosh Pit',
	    	'venue_address' => '123 Example Lane',
	    	'city' => 'Laraville',
	    	'state' => 'ON',
	    	'zip' => '17196',
	    	'additional_information' => 'For tickets, call (555) 555-5555.',
	    ]);

	    $response = $this->get(route('concerts.show', $concert->id));

	    $response->assertSee('The Red Chord');
	    $response->assertSee('with Animosity and Lethargy');
	    $response->assertSee('December 13, 2019');
	    $response->assertSee('8:00pm');
	    $response->assertSee('32.50');
	    $response->assertSee('The Mosh Pit');
	    $response->assertSee('123 Example Lane');
	    $response->assertSee('Laraville');
	    $response->assertSee('ON 17196');
	    $response->assertSee('For tickets, call (555) 555-5555.');
	}
}
