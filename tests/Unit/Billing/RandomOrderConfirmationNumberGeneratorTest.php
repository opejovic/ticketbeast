<?php

namespace Tests\Unit\Billing;

use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
	/** @test */
	function must_be_24_characters_long()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmationNumber = $generator->generate();

	    $this->assertEquals(24, strlen($confirmationNumber));
	}

	/** @test */
	function must_contain_only_uppercase_letters_and_numbers()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmationNumber = $generator->generate();

	    $this->assertRegExp('/^[A-Z0-9]+$/', $confirmationNumber);
	}

	// Cannot contain ambiguous characters 
	/** @test */
	function cannot_contain_ambiguous_characters()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmationNumber = $generator->generate();

	    $this->assertFalse(strpos($confirmationNumber, '1'));
	    $this->assertFalse(strpos($confirmationNumber, 'I'));
	    $this->assertFalse(strpos($confirmationNumber, '0'));
	    $this->assertFalse(strpos($confirmationNumber, 'O'));

	// Must be unique
	}
	/** @test */
	function every_confirmation_number_must_be_unique()
	{
	    $generator = new RandomOrderConfirmationNumberGenerator;

	    $confirmationNumbers = array_map(function ($i) use ($generator) {
	    	return $generator->generate();
	    }, range(1, 100));

		$this->assertCount(100, array_unique($confirmationNumbers));
	}


	// 
}
