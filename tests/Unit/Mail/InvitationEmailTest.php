<?php

namespace Tests\Unit\Mail;

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function email_contains_a_link_to_accept_the_invitation()
	{
	    $invitation = factory(Invitation::class)->create([
	    	'email' => 'john@example.com',
	    	'code' => 'TESTCODE1234',
	    ]);

	    $email = new InvitationEmail($invitation);
	    $this->assertContains(url('/invitations/TESTCODE1234'), $email->render());
	}

	/** @test */
	function email_has_the_correct_subject()
	{
	    $invitation = factory(Invitation::class)->create();

	    $email = new InvitationEmail($invitation);

	    $this->assertEquals('You are invited to join TicketBeast.', $email->build()->subject);
	}
}
