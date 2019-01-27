<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Reservation;
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
			$reservation = new Reservation($tickets);

			// Charge customer
			$this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));
			
			// Create order
			$order = Order::forTickets($tickets, request('email'), $reservation->totalCost());
			
			return response()->json($order, 201);

		} catch (PaymentFailedException $e) {
			return response()->json([], 422);
		} catch (NotEnoughTicketsException $e) {
			return response()->json([], 422);
		}
	}
}
