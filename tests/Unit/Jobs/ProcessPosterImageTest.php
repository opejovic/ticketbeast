<?php

namespace Tests\Unit\Jobs;

use App\Events\ConcertAdded;
use App\Helpers\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessPosterImageTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_resizes_the_poster_image_to_600px_wide()
	{
		Storage::fake('public');
		Storage::disk('public')->put(
			'posters/example-poster.png',
			file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
		);


	 	$concert = ConcertFactory::createUnpublished([
	 		'poster_image_path' => 'posters/example-poster.png',
		]);

		ConcertAdded::dispatch($concert);

		$resizedImage = Storage::disk('public')->get($concert->poster_image_path);
		list($width, $height) = getimagesizefromstring($resizedImage);

		$this->assertEquals(600, $width);
		$this->assertEquals(776, $height);

	    $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.png');
	    $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
	    $this->assertEquals($controlImageContents, $optimizedImageContents);
	}

	/** @test */
	function it_optimizes_the_poster_image_size()
	{
		Storage::fake('public');

		Storage::disk('public')->put(
			'posters/example-poster.png',
			file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
		);

	    $concert = ConcertFactory::createUnpublished([
	    	'poster_image_path' => 'posters/example-poster.png',
	    ]);

	    ConcertAdded::dispatch($concert);

	    $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.png');
	    $originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
	    $this->assertLessThan($originalSize, $optimizedImageSize);

	    $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.png');
	    $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
	    $this->assertEquals($controlImageContents, $optimizedImageContents);
	}
}
