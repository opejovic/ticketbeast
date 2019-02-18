<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function user_can_view_their_order_confirmation()
	{
	    $concert = factory(Concert::class)->create();
	    $order = factory(Order::class)->create();
	    $ticket = factory(Ticket::class)->create([
	    	'concert_id' => $concert->id,
	    	'order_id' => $order->id,
	    ]);

	    // get the orders page

	    // various assertions+
	}
}
