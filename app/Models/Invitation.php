<?php

namespace App\Models;

use App\Mail\InvitationEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Invitation extends Model
{
	protected $guarded = [];

	/**
	 * Invitation belongs to User
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public static function findByCode($code)
    {
    	return self::whereCode($code)->firstOrFail();
    }

    public function hasBeenUsed()
    {
    	return $this->user_id !== null;
    }

    // public function send()
    // {
    // 	Mail::to($this->email)->send(new InvitationEmail($this));
    // }
}
