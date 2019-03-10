<?php

namespace Tests\Unit\Jobs;

use App\Helpers\ConcertFactory;
use App\Helpers\OrderFactory;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendAttendeeMessageTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_sends_the_message_to_all_concert_attendees()
	{
		$this->withoutExceptionHandling();
		Mail::fake();
	 	$concert = ConcertFactory::createPublished();
	 	$otherConcert = ConcertFactory::createPublished();
	 	$message = AttendeeMessage::create([
	 		'concert_id' => $concert->id,
	 		'subject' => 'My Subject',
	 		'message' => 'My Message',
	 	]);

	 	$orderA = OrderFactory::createForConcert($concert, ['email' => 'john@example.com']);
	 	$orderB = OrderFactory::createForConcert($concert, ['email' => 'jane@example.com']);
	 	$orderC = OrderFactory::createForConcert($concert, ['email' => 'jim@example.com']);
	 	$orderD = OrderFactory::createForConcert($otherConcert, ['email' => 'other@example.com']);

	 	SendAttendeeMessage::dispatch($message);
	 	
	 	Mail::assertSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
	 		return $mail->hasTo('john@example.com')
	 			&& $mail->attendeeMessage->is($message);
	 	});

	 	Mail::assertSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
	 		return $mail->hasTo('jane@example.com')
	 			&& $mail->attendeeMessage->is($message);
	 	});

	 	Mail::assertSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
	 		return $mail->hasTo('jim@example.com')
	 			&& $mail->attendeeMessage->is($message);
	 	});

	 	Mail::assertNotSent(AttendeeMessageEmail::class, function ($mail) {
	 		return $mail->hasTo('other@example.com');
	 	});
	}
}
