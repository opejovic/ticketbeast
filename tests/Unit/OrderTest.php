<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function creating_an_order_from_tickets_email_and_amount()
	{
	    $tickets = factory(Ticket::class, 3)->create();
	    $charge = new Charge([
	    	'amount' => 3600,
	    	'card_last_four' => '1234',
	    ]);

	    $order = Order::forTickets($tickets, 'john@example.com', $charge);

	    $this->assertEquals('john@example.com', $order->email);
	    $this->assertEquals(3, $order->ticketQuantity());
	    $this->assertEquals(3600, $order->amount);
	    $this->assertEquals('1234', $order->card_last_four);
	}

	/** @test */
	function can_be_retrieved_by_confirmation_number()
	{
	    $order = factory(Order::class)->create(['confirmation_number' => 'RANDOMCONFIRMATIONNUMBER1234']);

	    $foundOrder = Order::findByConfirmationNumber('RANDOMCONFIRMATIONNUMBER1234');

	    $this->assertEquals($foundOrder->id, $order->id);
	}

	/** @test */
	function retrieving_a_nonexistent_order_throws_an_exception()
	{
	    $this->expectException(ModelNotFoundException::class);
	    Order::findByConfirmationNumber('NONEXISTENTCONFIRMATIONNUMBER');
	}

	/** @test */
	function converting_to_array()
	{
	    $order = factory(Order::class)->create([
	    	'confirmation_number' => 'ORDERCONFIRMATION1234',
	    	'email' => 'john@example.com',
	    	'amount' => 6000,
	    ]);

	    $order->tickets()->saveMany([
	    	factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
	    	factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
	    	factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
	    ]);

	    $result = $order->toArray();

	    $this->assertEquals([
	    	'confirmation_number' => 'ORDERCONFIRMATION1234',
	    	'email' => 'john@example.com',
	    	'amount' => 6000,
	    	'tickets' => [
	    		['code' => 'TICKETCODE1'],
	    		['code' => 'TICKETCODE2'],
	    		['code' => 'TICKETCODE3'],
	    	],
	    ], $result);
	}
}
