<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create(Concert $concert)
    {
    	$concert = Auth::user()->concerts()->published()->findOrFail($concert->id);

    	return view('backstage.concert-messages.new', ['concert' => $concert]);
    }

    public function store(Concert $concert)
    {
    	$concert = Auth::user()->concerts()->published()->findOrFail($concert->id);

    	$this->validate(request(),[
    		'subject' => ['required'],
    		'message' => ['required'],
    	]);

    	$message = $concert->attendeeMessages()->create(request(['subject', 'message']));

    	SendAttendeeMessage::dispatch($message);

    	return redirect()
    		->route('backstage.concert-messages.new',[
    			'concert' => $concert,
    		])->with('flash', 'Your message has been successfully saved.');
    }
}
