<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function promoters_can_view_the_add_concerts_form()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    /** @test */
    function guests_cannot_view_the_add_concerts_form()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function adding_a_valid_concert()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }

    /** @test */
    function guests_cannot_add_new_concerts()
    {
        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());

    }

    /** @test */
    function title_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => '',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

     /** @test */
    function subtitle_is_optional()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => '',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertNull($concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }

    /** @test */
    function additional_informaiton_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('additional_information');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function date_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function time_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function venue_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => '',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function venue_address_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function city_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => '',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('city');
        $this->assertEquals(0, Concert::count());
    }
 
    /** @test */
    function state_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => '',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('state');
        $this->assertEquals(0, Concert::count());
    }
    
    /** @test */
    function zip_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('zip');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function ticket_price_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function ticket_quantity_is_required()
    {
        $user = factory(User::class)->create();
        
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }
}
