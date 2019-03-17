<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
