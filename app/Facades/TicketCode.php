<?php

namespace App\Facades;

use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TicketCodeGenerator::class;
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string|null
     */
    protected static function getMockableClass()
    {
    	return static::getFacadeAccessor();
    }


}
