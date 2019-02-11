<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->lastCharge = $this->lastCharge();
	}

	private function lastCharge()
	{
		return \Stripe\Charge::all(
			[
				"limit" => 1,
			], 
			['api_key' => config('services.stripe.secret')]
		)['data'][0];		
	}

	private function newCharges()
	{
		return \Stripe\Charge::all(
			[
				"limit" => 1,
				"ending_before" => $this->lastCharge->id,
			], 
			['api_key' => config('services.stripe.secret')]
		)['data'];
	}

	/** @test */
	function charges_with_valid_payment_token_are_successful()
	{
		$paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

	    $charge = $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

	    $this->assertCount(1, $this->newCharges());
	    $this->assertEquals(2500, $this->lastCharge()->amount);
	}

	/** @test */
	function charges_with_invalid_payment_token_fail()
	{
		try {
			$paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
	    	$paymentGateway->charge(2500, "invalid-payment-token");			
		} catch (PaymentFailedException $e) {
	    	$this->assertCount(0, $this->newCharges());
			return;
		}
	
		$this->fail("Payment succeeded even though the payment token was invalid.");	    
	}
}
