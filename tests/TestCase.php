<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
    	parent::setUp();

    	Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function ($key) {
            return Assert::assertTrue($this->contains($key), "Failed asserting that the collection contained the specified value.");
        });

        EloquentCollection::macro('assertNotContains', function ($key) {
            return Assert::assertFalse($this->contains($key), "Failed asserting that the collection did not contain the specified value.");
        });

        EloquentCollection::macro('assertEquals', function ($items) {
            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });        

        EloquentCollection::macro('assertNotEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));
            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertFalse($a->is($b));
            });
        });
    }
}
