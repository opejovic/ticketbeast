<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function scopePublished($query)
    {
    	return $query->whereNotNull('published_at');
    }

    public function isPublished()
    {
    	return $this->published_at !== null;
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function reserveTickets($quantity, $email)
    {
        $tickets =  $this->findTickets($quantity)->each(function ($ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    public function orderTickets($email, $ticketQuantity)
    {
        $tickets = $this->findTickets($ticketQuantity);
        
        return $this->createOrder($email, $tickets);
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();
        throw_if($tickets->count() < $quantity, new NotEnoughTicketsException);

        return $tickets;
    }

    public function createOrder($email, $tickets)
    {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }
}
