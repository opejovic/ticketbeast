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

    public function orders()
    {
    	return $this->concert->orders();
    }

    public function withChunkedRecipients($chunkSize, $callback)
    {
    	return $this->orders()->chunk($chunkSize, function ($orders) use ($callback) {
    		$callback($orders->pluck('email'));
    	});	
    }
}
