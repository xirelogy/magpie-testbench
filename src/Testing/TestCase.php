<?php

namespace MagpieLib\TestBench\Testing;

use DateTimeInterface;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * A test case
 */
abstract class TestCase extends PhpUnitTestCase
{
    /**
     * Asserts that two date/time values are equal.
     * @param DateTimeInterface|null $expected
     * @param DateTimeInterface|null $actual
     * @param string $message
     * @return void
     */
    protected function assertTimeEquals(?DateTimeInterface $expected, ?DateTimeInterface $actual, string $message = '') : void
    {
        $this->assertEquals($expected?->getTimestamp(), $actual?->getTimestamp(), $message);
    }
}