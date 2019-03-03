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
	    $concert = factory(Concert::class)->create(['published_at' => null]);
	    $this->assertFalse($concert->isPublished());

	    $concert->publish();

	    $this->assertTrue($concert->isPublished());

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
	    $concert->tickets()->saveMany(factory(Ticket::class, 20)->create(['order_id' => 1]));
	    $concert->tickets()->saveMany(factory(Ticket::class, 10)->create(['order_id' => null]));

	    $this->assertEquals(10, $concert->ticketsRemaining());
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
