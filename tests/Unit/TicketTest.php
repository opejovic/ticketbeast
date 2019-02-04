<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_can_be_reserved()
	{
	    $ticket = factory(Ticket::class)->create();
	    $this->assertNull($ticket->reserved_at);

	    $ticket->reserve();

	    $this->assertNotNUll($ticket->fresh()->reserved_at);
	}

	/** @test */
	function it_can_be_released()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->addTickets(1);
	    $order = $concert->orderTickets('john@example.com', 1);
	    $ticket = $order->tickets()->first();
	    $this->assertEquals($order->id, $ticket->order_id);

	    $ticket->release();

	    $this->assertNull($ticket->fresh()->order_id);
	}
}
