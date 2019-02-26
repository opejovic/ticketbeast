<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
	/** @test */
	function email_must_contain_a_link_to_the_order_confirmation_page()
	{
	    $order = factory(Order::class)->make([
	    	'confirmation_code' => 'ORDERCONFIRMATION1234',
	    ]);

	    $email = new OrderConfirmationEmail($order);

	    $this->assertContains(url("orders/ORDERCONFIRMATION1234"), $email->render());
	}

	/** @test */
	function email_has_a_subject()
	{
	    $order = factory(Order::class)->make();

	    $email = new OrderConfirmationEmail($order);

	    $this->assertEquals("Your TicketBeast Order", $email->build()->subject);
	}

}
