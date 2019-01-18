<?php

namespace Tests\Unit;

use App\Models\Concert;
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
	    
	    $this->assertEquals('9:00pm', $concert->start_time);
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
	function can_order_concert_tickets()
	{
	    $concert = factory(Concert::class)->create();

	    $order = $concert->orderTickets('jane@example.com', 3);

	    $this->assertEquals('jane@example.com', $order->email);
	    $this->assertEquals(3, $order->tickets()->count());
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
	    $concert->addTickets(10);

	    $concert->orderTickets('jane@example.com', 8);

	    $this->assertEquals(2, $concert->ticketsRemaining());
	}
}
