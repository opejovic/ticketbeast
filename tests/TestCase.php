<?php

namespace Tests;

use Mockery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
    	parent::setUp();

    	Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }
}
