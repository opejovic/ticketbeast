<?php

namespace Tests\Feature\Backstage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_login_with_valid_credentials()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/backstage/concerts/new');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    function cannot_login_with_invalid_credentials()
    {
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret'),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrongemail@example.com',
            'password' => 'incorrect-password',
        ]);
     
        $response->assertSessionHasErrors('email');
        $response->assertRedirect('/login');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(Auth::check());
    }    

    /** @test */
    function cannot_login_with_non_existent_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'incorrect-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }

    /** @test */
    function logging_out_the_current_user()
    {
        $this->withoutExceptionHandling();
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
