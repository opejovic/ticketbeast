<?php

namespace App;

use App\OrderConfirmationNumberGenerator;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate()
    {
    	$pool = "ABCDEFGHJKLMNPQRSTUWVZY23456789";

    	return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
