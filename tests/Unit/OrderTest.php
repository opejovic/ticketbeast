<?php

namespace Tests\Unit;

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
	function creating_an_order_from_reservation()
	{
		$concert = factory(Concert::class)->create(['ticket_price' => 1200]);
		$tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
	    $reservation = new Reservation($tickets, 'john@example.com');

	    $order = Order::fromReservation($reservation);

	    $this->assertEquals('john@example.com', $order->email);
	    $this->assertEquals(3, $order->ticketQuantity());
	    $this->assertEquals(3600, $order->amount);
	}

	/** @test */
	function creating_an_order_from_tickets_email_and_amount()
	{
	    $concert = factory(Concert::class)->create()->addTickets(5);
	    $this->assertEquals(5, $concert->ticketsRemaining());

	    $order = Order::forTickets($concert->findTickets(3), 'john@example.com', 3600);

	    $this->assertEquals('john@example.com', $order->email);
	    $this->assertEquals(3, $order->ticketQuantity());
	    $this->assertEquals(3600, $order->amount);
	    $this->assertEquals(2, $concert->ticketsRemaining());
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
	    $concert = factory(Concert::class)->create(['ticket_price' => 2000])->addTickets(3);
	    $order = $concert->orderTickets('john@example.com', 3);

	    $this->assertEquals([
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'amount' => 6000,
	    ], $order->toArray());
	}
}
