<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_successfully()
    {
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'john@example.com')
                    ->type('password', 'secret')
                    ->press('Log in')
                    ->assertPathIs('/backstage/concerts/new');
        });
    }

    /** @test */
    public function logging_in_with_invalid_credentials()
    {
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'nobody@example.com')
                    ->type('password', 'invalid-password')
                    ->press('Log in')
                    ->assertPathIs('/login')
                    ->assertSee('credentials do not match');
        });
    }
}
