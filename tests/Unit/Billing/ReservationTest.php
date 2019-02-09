<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function retrieving_customers_email()
	{
	    $reservation = new Reservation(collect(), 'john@example.com');

	    $this->assertEquals('john@example.com', $reservation->email());
	}

	/** @test */
	function retrieving_tickets_from_reservation()
	{
		$tickets = collect([
			(object) ['price' => 1200],
			(object) ['price' => 1200],
			(object) ['price' => 1200],
		]);	

	    $reservation = new Reservation($tickets, 'john@example.com');

	    $this->assertEquals($tickets, $reservation->tickets());
	}

	/** @test */
	function completing_the_reservation()
	{
		$concert = factory(Concert::class)->create(['ticket_price' => 1200]);
		$tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
	    $reservation = new Reservation($tickets, 'john@example.com');
	    $paymentGateway = new FakePaymentGateway;

	    $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken());

       	$this->assertEquals('john@example.com', $order->email);
	    $this->assertEquals(3, $order->ticketQuantity());
	    $this->assertEquals(3600, $order->amount);
	    $this->assertEquals(3600, $paymentGateway->totalCharges());
	}

	/** @test */
	function cancelling_the_reservation_releases_the_tickets()
	{
		$tickets = collect([
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class),
			Mockery::spy(Ticket::class),
		]);

	 	$reservation = new Reservation($tickets, 'john@example.com');
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

	    $reservation = new Reservation($tickets, 'john@example.com');

	    $this->assertEquals(3600, $reservation->totalCost());
	}
}
