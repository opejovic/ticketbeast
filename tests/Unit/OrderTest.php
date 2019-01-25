<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
	use RefreshDatabase;

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

	/** @test */
	function tickets_are_released_when_order_is_cancelled()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->addTickets(10);
	    $order = $concert->orderTickets('john@example.com', 5);
	    $this->assertEquals(5, $concert->ticketsRemaining());

	    $order->cancel();

	    $this->assertEquals(10, $concert->ticketsRemaining());
	    $this->assertNull(Order::find($order->id));
	}
}
