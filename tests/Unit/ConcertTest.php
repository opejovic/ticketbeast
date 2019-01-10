<?php

namespace Tests\Unit;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConcertTest extends TestCase
{
	/** @test */
	function it_can_get_formatted_date()
	{
	    $concert = factory(Concert::class)->make(['date' => Carbon::parse('December 14, 2019 9:00pm')]);

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
}
