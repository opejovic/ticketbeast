<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractsTest
{
	abstract protected function getPaymentGateway();

	/** @test */
	function charges_with_valid_payment_token_are_successful()
	{
		$paymentGateway = $this->getPaymentGateway();
	    $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
		    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
	    });

	    $this->assertCount(1, $newCharges);
	    $this->assertEquals(2500, $newCharges->map->amount()->sum());
	}

	/** @test */
	function can_fetch_charges_during_a_callback()
	{
		$paymentGateway = $this->getPaymentGateway();
    	$paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
    	$paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

		$newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
	    	$paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
	    	$paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
		});

		$this->assertCount(2, $newCharges);
	    $this->assertEquals([5000, 4000], $newCharges->map->amount()->all());
	}

	/** @test */
	function can_get_details_about_a_successful_charge()
	{
	    $paymentGateway = $this->getPaymentGateway();

	    $charge = $paymentGateway->charge(2500, $paymentGateway->getValidTestToken('4242424242424242'));

	    $this->assertEquals('4242', $charge->cardLastFour());
	    $this->assertEquals(2500, $charge->amount());
	}

	
	/** @test */
	function charges_with_invalid_payment_token_fail()
	{
		$paymentGateway = $this->getPaymentGateway();
		
		$newCharges = $paymentGateway->newChargesDuring( function ($paymentGateway) {

			try { 
		    	$paymentGateway->charge(2500, "invalid-payment-token");			
			} catch (PaymentFailedException $e) {
				return;
			}
			
			$this->fail("Charge succeeded even though the payment token was invalid.");	    
			
		});
    		$this->assertCount(0, $newCharges);
	
	}
}
