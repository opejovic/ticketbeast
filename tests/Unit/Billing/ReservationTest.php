<?php

namespace Tests\Unit\Billing;

use Mockery;
use App\Models\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function cancelling_the_reservation_releases_the_tickets()
	{
		$tickets = collect([
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class),
		]);

	 	$reservation = new Reservation($tickets);
	 	$reservation->cancel();   

	 	foreach ($tickets as $ticket) {
	 		$ticket->shouldHaveReceived('release');
	 	}
	}

	/** @test */
	function calculating_the_total_cost()
	{
		$tickets = collect([
			(object) ['price' => 1200],
			(object) ['price' => 1200],
			(object) ['price' => 1200],
		]);

	    $reservation = new Reservation($tickets);

	    $this->assertEquals(3600, $reservation->totalCost());
	}
}
