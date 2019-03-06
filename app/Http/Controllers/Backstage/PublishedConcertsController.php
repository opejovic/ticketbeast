<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;

class PublishedConcertsController extends Controller
{
    public function store()
    {
    	$concert = Concert::where('user_id', auth()->id())->findOrFail(request('concert_id'));

    	abort_if($concert->isPublished(), 422);
    	$concert->publish();

    	return redirect()->route('backstage.concerts.index');
    }
}
