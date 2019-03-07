<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index(Concert $concert)
    {
    	$concert = Auth::user()->concerts()->published()->findOrFail($concert->id);

    	return view('backstage.published-concert-orders.index', ['concert' => $concert]);
    }
}
