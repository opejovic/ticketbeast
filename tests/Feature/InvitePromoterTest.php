<?php

namespace Tests\Feature;

use App\Facades\InvitationCode;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function inviting_the_promoter_via_the_cli()
    {
        Mail::fake();
        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE1234');
        $this->artisan('invite-promoter', ['email' => 'john@example.com']);

        $this->assertEquals(1, Invitation::count());
        $invitation = Invitation::first();
        $this->assertEquals('john@example.com', $invitation->email);
        $this->assertEquals('TESTCODE1234', $invitation->code);

        Mail::assertSent(InvitationEmail::class, function ($email) use ($invitation) {
            return $email->hasTo('john@example.com') 
                && $email->invitation->is($invitation);
        });
    }
}
