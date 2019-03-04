<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => auth()->user()->concerts()->get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backstage.concerts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'                  => ['required'],
            'date'                   => ['required', 'date'],
            'time'                   => ['required', 'date_format:g:ia'],
            'venue'                  => ['required'],
            'venue_address'          => ['required'],
            'city'                   => ['required'],
            'state'                  => ['required'],
            'zip'                    => ['required'],
            'ticket_price'           => ['required', 'numeric', 'min:5'],
            'ticket_quantity'        => ['required', 'numeric', 'min:1'],
        ]);

        $concert = auth()->user()->concerts()->create([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time')
            ])),
            'ticket_price' => request('ticket_price') * 100,
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information'),
        ])->addTickets(request('ticket_quantity'));

        $concert->publish();

        return redirect()->route('concerts.show', $concert);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Concert  $concert
     * @return \Illuminate\Http\Response
     */
    public function show(Concert $concert)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Concert  $concert
     * @return \Illuminate\Http\Response
     */
    public function edit(Concert $concert)
    {
        $concert = Auth::user()->concerts()->findOrFail($concert->id);

        abort_if($concert->isPublished(), 403);

        return view('backstage.concerts.edit', ['concert' => $concert]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Concert  $concert
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Concert $concert)
    {
        $request->validate([
            'title'                  => ['required'],
            'date'                   => ['required', 'date'],
            'time'                   => ['required', 'date_format:g:ia'],
            'venue'                  => ['required'],
            'venue_address'          => ['required'],
            'city'                   => ['required'],
            'state'                  => ['required'],
            'zip'                    => ['required'],
            'ticket_price'           => ['required', 'numeric', 'min:4'],
            'ticket_quantity'        => ['required', 'numeric', 'min:1'],
        ]);

        $concert = Auth::user()->concerts()->findOrFail($concert->id);
        
        abort_if($concert->isPublished(), 403);

        $concert->update([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time')
            ])),
            'ticket_price' => request('ticket_price') * 100,
            'ticket_quantity' => (int) request('ticket_quantity'),
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Concert  $concert
     * @return \Illuminate\Http\Response
     */
    public function destroy(Concert $concert)
    {
        //
    }
}
