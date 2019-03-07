<?php

namespace Tests\Feature\Backstage;

use App\Helpers\ConcertFactory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concerts()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_promoters_cannot_view_the_orders_of_other_promoter_concerts()
    {
        $promoterA = factory(User::class)->create();
        $promoterB = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $promoterB->id]);

        $response = $this->actingAs($promoterA)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}   
