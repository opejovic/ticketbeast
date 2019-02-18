<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function show($confirmationNumber)
    {
    	return view('orders.show', ['order' => Order::findByConfirmationNumber($confirmationNumber)]);
    }
}
