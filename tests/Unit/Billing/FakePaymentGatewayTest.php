<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
	/** @test */
	function charges_with_valid_payment_token_are_successful()
	{
	    $paymentGateway = new FakePaymentGateway;

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

	    $this->assertEquals(2500, $paymentGateway->totalCharges());
	}
	
	/** @test */
	function charges_with_invalid_payment_token_fail()
	{
		try {
    		$paymentGateway = new FakePaymentGateway;
	    	$paymentGateway->charge(2500, "invalid-payment-token");			
		} catch (PaymentFailedException $e) {
	    	$this->assertEquals(0, $paymentGateway->totalCharges());
			return;
		}
	
		$this->fail("Payment succeeded even though the payment token was invalid");	    
	}

	/** @test */
	function running_a_hook_before_first_charge()
	{
	    $paymentGateway = new FakePaymentGateway;
	    $timesCallbackRan = 0;

	    $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
	    	$timesCallbackRan++;
	    	$paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
	    	$this->assertEquals(2500, $paymentGateway->totalCharges());
	    });

	    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
	    $this->assertEquals(1, $timesCallbackRan);
	    $this->assertEquals(5000, $paymentGateway->totalCharges());
	}
}
