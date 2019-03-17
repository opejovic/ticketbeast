<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function viewing_unused_invitation()
    {
        $this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);


        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->viewData('invitation')->is($invitation));
    }

    /** @test */
    function viewing_used_invitations()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create()->id,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    /** @test */
    function viewing_non_existing_invitations()
    {
        $response = $this->get('/invitations/NONEXISTINGCODE');

        $response->assertStatus(404);
    }

    /** @test */
    function registering_with_a_valid_invitation_code()
    {
        $this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($invitation->fresh()->user->is($user));
    }
}
