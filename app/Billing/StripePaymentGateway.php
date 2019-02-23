<?php

namespace App\Billing;

use App\Billing\PaymentFailedException;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

    public function charge($amount, $token)
    {
    	try {
			$stripeCharge = \Stripe\Charge::create([
				"amount" => $amount,
				"currency" => "usd",
				"source" => $token,
			], ['api_key' => $this->apiKey]);   	

			return new Charge([
				'amount' => $stripeCharge['amount'],
				'card_last_four' => $stripeCharge['source']['last4'],
			]);
    	} catch (InvalidRequest $e) {
			throw new PaymentFailedException;	    		
    	}
    }

    public function getValidTestToken()
    {
		return \Stripe\Token::create([
			"card" => [
				"number" => "4242424242424242",
				"exp_month" => 1,
				"exp_year" => date('Y') + 1,
				"cvc" => "123"
			]
		], ['api_key' => $this->apiKey])->id;
    }

	private function lastCharge()
	{
		return \Stripe\Charge::all(
			[
				"limit" => 1,
			], 
			['api_key' => $this->apiKey]
		)['data'][0];		
	}

	private function newChargesSince($charge = null)
	{
		return collect(\Stripe\Charge::all(
			[
				"ending_before" => $charge ? $charge->id : null,
			], 
			['api_key' => $this->apiKey]
		)['data']);
	}

    public function newChargesDuring($callback)
    {
    	$lastCharge = $this->lastCharge();
    	$callback($this);
    	return $this->newChargesSince($lastCharge)->map(function ($stripeCharge) {
    		return new Charge([
				'amount' => $stripeCharge['amount'],
				'card_last_four' => $stripeCharge['source']['last4'],
			]);
    	});
    }
}
