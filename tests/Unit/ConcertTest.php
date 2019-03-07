<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConcertTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_can_get_formatted_date()
	{
	    $concert = factory(Concert::class)->make(['date' => Carbon::parse('2019-12-14 9:00pm')]);

	    $this->assertEquals('December 14, 2019', $concert->formatted_date);
	}

	/** @test */
	function it_can_get_formatted_start_time()
	{
	    $concert = factory(Concert::class)->make(['date' => Carbon::parse('December 14, 2019 21:00')]);
	    
	    $this->assertEquals('9:00pm', $concert->formatted_start_time);
	}

	/** @test */
	function it_can_get_ticket_price_in_dollars()
	{
	    $concert = factory(Concert::class)->make(['ticket_price' => 3250]);

	    $this->assertEquals('32.50', $concert->ticket_price_in_dollars);
	}

	/** @test */
	function concerts_with_published_at_date_are_published()
	{
	    $publishedConcertA = factory(Concert::class)->states('published')->create();
	    $publishedConcertB = factory(Concert::class)->states('published')->create();
	    $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

	    $publishedConcerts = Concert::published()->get();

	    $this->assertTrue($publishedConcerts->contains($publishedConcertA));
	    $this->assertTrue($publishedConcerts->contains($publishedConcertB));
	    $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
	}

	/** @test */
	function concerts_can_be_published()
	{
	    $concert = factory(Concert::class)->create([
	    	'published_at' => null,
	    	'ticket_quantity' => 5,
	    ]);

	    $this->assertFalse($concert->isPublished());
	    $this->assertEquals(0, $concert->ticketsRemaining());

	    $concert->publish();

	    $this->assertTrue($concert->isPublished());
	    $this->assertEquals(5, $concert->ticketsRemaining());
	}

	/** @test */
	function can_reserve_tickets()
	{
	    $concert = factory(Concert::class)->create()->addTickets(10);

	    $reservation = $concert->reserveTickets(8, 'john@example.com');

	    $this->assertEquals(8, $reservation->tickets()->count());
	    $this->assertEquals(2, $concert->ticketsRemaining());
	}

	/** @test */
	function can_add_tickets()
	{
	    $concert = factory(Concert::class)->create();

	    $concert->addTickets(10);

	    $this->assertEquals(10, $concert->ticketsRemaining());
	}

	/** @test */
	function tickets_remaining_does_not_include_tickets_associated_with_an_order()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

	    $this->assertEquals(2, $concert->ticketsRemaining());
	}

	/** @test */
	function tickets_sold_only_include_tickets_associated_with_an_order()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));
	    
	    $this->assertEquals(3, $concert->ticketsSold());
	    $this->assertEquals(2, $concert->ticketsRemaining());
	}

	/** @test */
	function total_tickets_includes_all_tickets()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));
	    
	    $this->assertEquals(5, $concert->totalTickets());
	}

	/** @test */
	function calculating_the_percentage_of_tickets_sold()
	{
	    $concert = factory(Concert::class)->create();
	    $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => 1]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => null]));
	    
	    // $this->assertEquals(0.2857142, $concert->percentSoldOut(), '', 0.00001);
	    $this->assertEquals(28.57, $concert->percentSoldOut());
	}

	/** @test */
	function calculating_the_revenue_in_dollars()
	{
	    $concert = factory(Concert::class)->create();
	    $orderA = factory(Order::class)->create(['amount' => 4200]);
	    $orderB = factory(Order::class)->create(['amount' => 3250]);

	    $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => $orderA->id]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => $orderB->id]));
	    $this->assertEquals(74.50, $concert->revenueInDollars());
	}

	/** @test */
	function trying_to_reserve_more_tickets_than_remain_throws_an_Exception()
	{
	    $concert = factory(Concert::class)->create()->addTickets(10);

	    try {
			$concert->reserveTickets('11', 'jane@example.com');
	    } catch (NotEnoughTicketsException $e) {
	    	$this->assertNull($concert->orders()->where('email', 'jane@example.com')->first());
	    	$this->assertEquals(10, $concert->ticketsRemaining());
	    	return;

	    }

	    $this->fail("Order created even though there were not enough tickets remaining.");
	}

	/** @test */
	function cannot_reserve_tickets_that_have_already_been_purchased()
	{
	    $concert = factory(Concert::class)->create()->addTickets(10);
	    $order = factory(Order::class)->create();
	    $order->tickets()->saveMany($concert->tickets->take(8));

	    try {
	    	$concert->reserveTickets(3, 'john@example.com');
	    } catch (NotEnoughTicketsException $e) {
	    	$this->assertEquals(2, $concert->ticketsRemaining());
	    	return;
	    }

	    $this->fail("Reservation succeeded even though the tickets have already been purchased.");
	}

	/** @test */
	function cannot_reserve_tickets_that_have_already_been_reserved()
	{
	    $concert = factory(Concert::class)->create()->addTickets(10);
	    $concert->reserveTickets(8, 'john@example.com');

	    try {
	    	$concert->reserveTickets(3, 'jane@example.com');
	    } catch (NotEnoughTicketsException $e) {
	    	$this->assertEquals(2, $concert->ticketsRemaining());
	    	return;
	    }

	    $this->fail("Reservation succeeded even though the tickets have already been reserved.");
	}
}
