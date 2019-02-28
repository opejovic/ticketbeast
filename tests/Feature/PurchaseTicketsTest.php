<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use App\Models\Concert;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
	    $this->paymentGateway = new FakePaymentGateway;
	    $this->app->instance(PaymentGateway::class, $this->paymentGateway);
	    Mail::fake();
	}

	private function orderTickets($concert, $params)
	{
		return $this->JSON('POST', "/concerts/$concert->id/orders", $params);
	}

	/** @test */
	function customer_can_purchase_tickets_to_a_published_concert()
	{
		$this->withoutExceptionHandling();
	    OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
	    TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000])->addTickets(3);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(201);
	    $response->assertJson([
	 		'confirmation_number' => 'ORDERCONFIRMATION1234',
	    	'email' => 'john@example.com',
	    	'amount' => 9000,
	    	'tickets' => [
	    		['code' => 'TICKETCODE1'],
	    		['code' => 'TICKETCODE2'],
	    		['code' => 'TICKETCODE3'],
	    	],
	    ]);

	    $this->assertEquals(9000, $this->paymentGateway->totalCharges());
	    
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(3, $order->tickets()->count());
	
	    Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order) {
	    	return $mail->hasTo('john@example.com') && $mail->order->id == $order->id;
	    });
	}

	/** @test */
	function customer_cannot_purchase_concert_tickets_to_an_unpublished_concert()
	{
	    $concert = factory(Concert::class)->states('unpublished')->create(['ticket_price' => 3000])->addTickets(3);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(404);
	    $this->assertEquals(0, $concert->orders()->count());
	    $this->assertEquals(0, $this->paymentGateway->totalCharges());
	}

	/** @test */
	function cannot_purchase_tickets_another_customer_is_trying_to_purchase()
	{
		$this->withoutExceptionHandling();

		$concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000])->addTickets(10);

		$this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
	
			$personA = $this->app['request'];

			$response = $this->orderTickets($concert, [
		    	'email' => 'personB@example.com',
		    	'ticket_quantity' => 3,
		    	'payment_token' => $this->paymentGateway->getValidTestToken(),
		    ]);

			$this->app['request'] = $personA;

		    $response->assertStatus(422);
		    $this->assertNull($concert->orders()->where('email', 'personB@example.com')->first());
		    $this->assertEquals(0, $this->paymentGateway->totalCharges());
		    $this->assertEquals(2, $concert->ticketsRemaining());
		});

		$response = $this->orderTickets($concert, [
	    	'email' => 'personA@example.com',
	    	'ticket_quantity' => 8,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(201);
	    $order = $concert->orders()->where('email', 'personA@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(8, $order->ticketQuantity());
	    $this->assertEquals(24000, $order->amount);
	    $this->assertEquals(2, $concert->ticketsRemaining());
	}

	/** @test */
	function cannot_purchase_more_tickets_than_remain()
	{
		$this->withoutExceptionHandling();
	    $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 51,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(422);
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNull($order);
	    $this->assertEquals(0, $this->paymentGateway->totalCharges());
	    $this->assertEquals(50, $concert->ticketsRemaining());
	}

	/** @test */
	function an_order_is_not_created_if_payment_fails()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000])->addTickets(3);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'payment_token' => "invalid-payment-token",
	    ]);

	    $response->assertStatus(422);
	    $this->assertEquals(0, $this->paymentGateway->totalCharges());
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNull($order);
	    $this->assertEquals(3, $concert->ticketsRemaining());
	}

	/** @test */
	function email_is_required_to_purchase_concert_tickets()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'ticket_quantity' => 3,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(422);
	    $response->assertJsonValidationErrors('email');
	}

	/** @test */
	function email_should_be_a_valid_email_address_to_purchase_concert_tickets()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'invalid-email-address',
	    	'ticket_quantity' => 3,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(422);
	    $response->assertJsonValidationErrors('email');
	}

	/** @test */
	function ticket_quantity_is_required_to_purchase_concert_tickets()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(422);
	    $response->assertJsonValidationErrors('ticket_quantity');
	}

	/** @test */
	function ticket_quantity_should_be_atleast_1_to_purchase_concert_tickets()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 0,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $response->assertStatus(422);
	    $response->assertJsonValidationErrors('ticket_quantity');
	}

	/** @test */
	function payment_token_is_required_to_purchase_concert_tickets()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 0,
	    ]);

	    $response->assertStatus(422);
	    $response->assertJsonValidationErrors('payment_token');
	}
}
