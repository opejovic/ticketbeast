<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function show($code)
    {
    	$invitation = Invitation::findByCode($code);

    	abort_if($invitation->hasBeenUsed(), 404);

    	return view('invitations.show', [
    		'invitation' => $invitation
    	]);
    }
}
