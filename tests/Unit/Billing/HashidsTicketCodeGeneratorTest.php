<?php

namespace Tests\Unit\Billing;

use App\HashidsTicketCodeGenerator;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HashidsTicketCodeGeneratorTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_must_be_atleast_6_characters_long()
	{
	    $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
	    $ticket = factory(Ticket::class)->create();

	    $code = $ticketCodeGenerator->generateFor($ticket);

	    $this->assertTrue(strlen($code) >= 6);
	}

	/** @test */
	function it_must_contain_only_uppercase_letters()
	{
	    $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

	    $ticket = factory(Ticket::class)->create();

	    $code = $ticketCodeGenerator->generateFor($ticket);

	    $this->assertRegExp('/^[A-Z]+$/', $code);
	}

	/** @test */
	function ticket_codes_generated_for_the_same_ticket_id_are_the_same()
	{
	    $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
	    
	    $ticket = factory(Ticket::class)->create(['id' => 1]);

	    $code1 = $ticketCodeGenerator->generateFor($ticket);
	    $code2 = $ticketCodeGenerator->generateFor($ticket);

	    $this->assertEquals($code1, $code2);
	}

	/** @test */
	function ticket_codes_generated_with_different_salts_for_the_same_ticket_id_are_different()
	{
	    $ticketCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
	    $ticketCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');

	    $ticket = factory(Ticket::class)->create(['id' => 1]);

	    $code1 = $ticketCodeGenerator1->generateFor($ticket);
	    $code2 = $ticketCodeGenerator2->generateFor($ticket);

	    $this->assertNotEquals($code1, $code2);
	}
}
