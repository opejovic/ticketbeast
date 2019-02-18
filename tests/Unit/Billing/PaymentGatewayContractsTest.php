<?php

namespace Tests\Unit\Billing;

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
	    $this->assertEquals(2500, $newCharges->sum());
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
	    $this->assertEquals([5000, 4000], $newCharges->all());
	}

	
	/** @test */
	function charges_with_invalid_payment_token_fail2()
	{
		$paymentGateway = $this->getPaymentGateway();
		
		$newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
			
			try 
		    	$paymentGateway->charge(2500, "invalid-payment-token");			
			} catch (PaymentFailedException $e) {
				return;
			}
			
			$this->fail("Charge succeeded even though the payment token was invalid.");	    
		});
	
    	$this->assertCount(0, $newCharges);
	}

}