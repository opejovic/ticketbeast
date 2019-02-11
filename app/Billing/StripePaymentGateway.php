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
			\Stripe\Charge::create([
				"amount" => $amount,
				"currency" => "usd",
				"source" => $token,
			], ['api_key' => $this->apiKey]);   	
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
}