<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function recipients()
    {
    	return $this->concert->orders()->pluck('email');
    }
}
