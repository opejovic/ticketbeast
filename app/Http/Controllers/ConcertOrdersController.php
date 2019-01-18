<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}

	public function store(Concert $concert)
	{
		abort_unless($concert->isPublished(), 404);

		request()->validate([
			'email' 		  => ['required', 'email'],
			'ticket_quantity' => ['required', 'integer', 'min:1'],
			'payment_token'   => ['required'],
		]);

		try {
			$concert->orderTickets(request('email'), request('ticket_quantity'));
			$this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));
			return response()->json([], 200);
		} catch (PaymentFailedException $e) {
			return response()->json([], 422);
		} catch (NotEnoughTicketsException $e) {
			return response()->json([], 422);
		}
	}
}
