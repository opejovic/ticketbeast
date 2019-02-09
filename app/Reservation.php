<?php

namespace App;

use App\Models\Order;

class Reservation
{
	private $tickets;
    private $email;

	public function __construct($tickets, $email)
	{
		$this->tickets = $tickets;
        $this->email = $email;
	}

    public function complete($paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);
        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }

    public function totalCost()
    {
    	return $this->tickets->sum('price');
    }

    public function email()
    {
        return $this->email;
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function cancel()
    {
    	foreach ($this->tickets as $ticket) {
    		$ticket->release();
    	}
    }
}
