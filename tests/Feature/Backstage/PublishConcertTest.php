<?php

namespace Tests\Feature\Backstage;

use App\Helpers\ConcertFactory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function promoter_can_publish_their_own_concerts()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id, 'ticket_quantity' => 3]);

        $response = $this->actingAs($user)->post("/backstage/published-concerts", [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts');
        $concert = $concert->fresh();
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    function concert_can_be_published_only_once()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id, 'ticket_quantity' => 3]);

        $response = $this->actingAs($user)->post("/backstage/published-concerts", [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(422);

        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    function a_promoter_cannot_publish_concerts_belonging_to_other_promoters()
    {
        $promoterA = factory(User::class)->create();
        $promoterB = factory(User::class)->create();

        $concert = ConcertFactory::createUnpublished(['user_id' => $promoterA->id, 'ticket_quantity' => 3]);

        $response = $this->actingAs($promoterB)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    function guests_cannot_publish_concerts()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id, 'ticket_quantity' => 3]);

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    function concerts_that_dont_exist_cant_be_published()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 99999,
        ]);

        $response->assertStatus(404);
    }
}
