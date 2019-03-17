<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public static function findByCode($code)
    {
    	return self::whereCode($code)->firstOrFail();
    }

    public function hasBeenUsed()
    {
    	return $this->user_id !== null;
    }
}
