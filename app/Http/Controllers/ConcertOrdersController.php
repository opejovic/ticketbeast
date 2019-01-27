<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
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
			// Find tickets
			$tickets = $concert->findTickets(request('ticket_quantity'));

			// Charge customer
			$this->paymentGateway->charge($tickets->sum('price'), request('payment_token'));
			
			// Create order
			$order = Order::forTickets($tickets, request('email'), $tickets->sum('price'));
			
			return response()->json($order, 201);

		} catch (PaymentFailedException $e) {
			return response()->json([], 422);
		} catch (NotEnoughTicketsException $e) {
			return response()->json([], 422);
		}
	}
}
