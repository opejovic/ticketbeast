<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function user_can_view_their_order_confirmation()
	{
		$this->withoutExceptionHandling();
	    $concert = factory(Concert::class)->create([
	        'title' => 'The Fake Band Name',
	    	'subtitle' => 'with The Fake Openers',
	    	'date' => Carbon::parse('2019-12-31 20:00'),
	    	'ticket_price' => 2000,
	    	'venue' => 'The Fake Venue',
	    	'venue_address' => '123 Example Lane',
	    	'city' => 'Fakerville',
	    	'state' => 'FV',
	    	'zip' => '123456',
	    ]);

	    $order = factory(Order::class)->create([
	    	'confirmation_number' => 'ORDERCONFIRMATION1234',
	    	'amount' => 5000,
	    	'card_last_four' => '4242',
	    	'email' => 'john@example.com',
	    ]);

	    $ticketA = factory(Ticket::class)->create([
	    	'concert_id' => $concert->id,
	    	'order_id' => $order->id,
	    	'code' => 'TICKETCODE123',
	    ]);

	    $ticketB = factory(Ticket::class)->create([
	    	'concert_id' => $concert->id,
	    	'order_id' => $order->id,
	    	'code' => 'TICKETCODE456',
	    ]);

	    // get the orders page
	    $response = $this->get("/orders/ORDERCONFIRMATION1234");

	    // various assertions+
	    $response->assertStatus(200);
	    $response->assertViewHas('order', $order);
	    $response->assertSee('ORDERCONFIRMATION1234');
	    $response->assertSee('$50.00');
	    $response->assertSee('**** **** **** 4242');
	    $response->assertSee('TICKETCODE123');
	    $response->assertSee('TICKETCODE456');
	    $response->assertSee('The Fake Band Name');
	    $response->assertSee('with The Fake Openers');
	    $response->assertSee('The Fake Venue');
	    $response->assertSee('123 Example Lane');
	    $response->assertSee('Fakerville, FV');
	    $response->assertSee('123456');
	    $response->assertSee('john@example.com');
	    
	    $response->assertSee('2019-12-31 20:00');

	}
}
