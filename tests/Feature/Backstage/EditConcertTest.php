<?php

namespace Tests\Feature\Backstage;

use App\Helpers\ConcertFactory;
use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });
    }

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'date' => Carbon::parse('2017-01-01 8:00pm'),
            'ticket_price' => 2000,
            'ticket_quantity' => 10,
            'venue' => 'Old venue name',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '000000',
            'additional_information' => 'Old additional information.', 
            'ticket_quantity' => 10, 
        ], $overrides);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'date' => '2017-01-01',
            'time' => '9:00pm',
            'ticket_price' => '25.00',
            'ticket_quantity' => '15',
            'venue' => 'New venue name',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '11111',
            'additional_information' => 'New additional information.',    
        ], $overrides);
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_other_promoters_concerts()
    {
        $promoterA = factory(User::class)->create();
        $promoterB = factory(User::class)->create();
        $concertB = factory(Concert::class)->create(['user_id' => $promoterB->id]);

        $response = $this->actingAs($promoterA)->get("/backstage/concerts/{$concertB->id}/edit");
        $response->assertStatus(404);
    }

    /** @test */
    function promoters_see_a_404_when_they_try_to_visit_the_edit_page_for_the_concert_that_doesent_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        
        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_doesent_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id
        ]));
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'date' => '2020-01-01',
            'time' => '9:00pm',
            'ticket_price' => '25.00',
            'venue' => 'New venue name',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '11111',
            'additional_information' => 'New additional information.',
            'ticket_quantity' => '15',
        ]);

        $response->assertRedirect("/backstage/concerts");

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals(Carbon::parse('2020-01-01 9:00pm'), $concert->date);
            $this->assertEquals('25.00', $concert->ticket_price_in_dollars);
            $this->assertEquals('15', $concert->ticket_quantity);
            $this->assertEquals('New venue name', $concert->venue);
            $this->assertEquals('New venue address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('11111', $concert->zip);
            $this->assertEquals('New additional information.', $concert->additional_information);
            $this->assertEquals('15', $concert->ticket_quantity);
        });
    }

    /** @test */
    function promoters_cannot_edit_other_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $otherUser->id,
        ]));
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);
        $this->assertArraySubset($this->oldAttributes(['user_id' => $otherUser->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function promoters_cannot_edit_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);
        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function guests_cannot_edit_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));
        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function title_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'title' => ''
            ]));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function subtitle_is_optional()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'subtitle' => '',
            ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts");

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('', $concert->subtitle);
            $this->assertEquals(Carbon::parse('2017-01-01 9:00pm'), $concert->date);
            $this->assertEquals('25.00', $concert->ticket_price_in_dollars);
            $this->assertEquals('15', $concert->ticket_quantity);
            $this->assertEquals('New venue name', $concert->venue);
            $this->assertEquals('New venue address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('11111', $concert->zip);
            $this->assertEquals('New additional information.', $concert->additional_information);
        });
    }


    /** @test */
    function date_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'date' => ''
            ]));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function ticket_price_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_price' => ''
            ]));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');

        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function ticket_quantity_is_required_and_it_must_be_atleast_1()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_quantity' => '0'
            ]));

        $response->assertRedirect("backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }
}
