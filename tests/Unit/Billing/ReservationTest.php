<?php

namespace Tests\Unit\Billing;

use App\Models\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function calculating_the_total_cost()
	{
	    $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(3);
	    $tickets = $concert->findTickets(3);

	    $reservation = new Reservation($tickets);

	    $this->assertEquals(3600, $reservation->totalCost());
	}
}
