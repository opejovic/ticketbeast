<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp()
	{
		parent::setUp();
	    $this->paymentGateway = new FakePaymentGateway;
	    $this->app->instance(PaymentGateway::class, $this->paymentGateway);
	}

	private function orderTickets($concert, $params)
	{
		return $this->JSON('POST', "/concerts/$concert->id/orders", $params);
	}

	/** @test */
	function customer_can_purchase_tickets_to_a_published_concert()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'payment_token' => $this->paymentGateway->getValidTestToken(),
	    ]);

	    $this->assertEquals(9000, $this->paymentGateway->totalCharges());
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNotNull($order);
	    $this->assertEquals(3, $order->tickets()->count());
	}

	/** @test */
	function customer_cannot_purchase_concert_tickets_to_an_unpublished_concert()
	{
	    $concert = factory(Concert::class)->states('unpublished')->create(['ticket_price' => 3000]);

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
	function an_order_is_not_created_if_payment_fails()
	{
	    $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000]);

	    $response = $this->orderTickets($concert, [
	    	'email' => 'john@example.com',
	    	'ticket_quantity' => 3,
	    	'payment_token' => "invalid-payment-token",
	    ]);

	    $response->assertStatus(422);
	    $this->assertEquals(0, $this->paymentGateway->totalCharges());
	    $order = $concert->orders()->where('email', 'john@example.com')->first();
	    $this->assertNull($order);

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
