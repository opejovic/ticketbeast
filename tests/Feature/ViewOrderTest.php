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
		$this->withoutExceptionHandling();
	    $concert = factory(Concert::class)->create();
	    $order = factory(Order::class)->create([
	    	'confirmation_number' => 'ORDERCONFIRMATION1234',
	    ]);
	    $ticket = factory(Ticket::class)->create([
	    	'concert_id' => $concert->id,
	    	'order_id' => $order->id,
	    ]);

	    // get the orders page
	    $response = $this->get("/orders/ORDERCONFIRMATION1234");

	    // various assertions+
	    $response->assertStatus(200);
	}
}
