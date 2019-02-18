<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Unit\Billing\PaymentGatewayContractsTest;

class FakePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new FakePaymentGateway;
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
