<?php

namespace MagpieLib\TestBench\Testing;

use DateTimeInterface;
use Exception;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Contexts\ScopedCollection;
use MagpieLib\TestBench\Http\Defaults\DefaultMockHttpClient;
use MagpieLib\TestBench\Http\MockHttpClient;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Throwable;

/**
 * A test case
 */
abstract class TestCase extends PhpUnitTestCase
{
    /**
     * @var bool If ran in scope
     */
    private bool $isRunInScope = false;


    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        // Reset
        $this->isRunInScope = false;
    }


    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function tearDown() : void
    {
        parent::tearDown();

        if (static::isForceRunInScope() && !$this->isRunInScope) {
            throw new Exception('test should be executed in runInScope()');
        }
    }


    /**
     * All scoped items
     * @return iterable<Scoped>
     */
    protected function getScopedItems() : iterable
    {
        return [];
    }


    /**
     * Run in scope
     * @param callable():T $fn
     * @return T
     * @template T
     * @throws Throwable
     */
    protected final function runInScope(callable $fn) : mixed
    {
        // Flag
        $this->isRunInScope = true;

        // Setup scope
        $scoped = new ScopedCollection($this->getScopedItems());

        try {
            $ret = $fn();
            $scoped->succeeded();
            return $ret;
        } catch (Throwable $ex) {
            $scoped->crash($ex);
            throw $ex;
        } finally {
            $scoped->release();
        }
    }


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


    /**
     * Access to an HTTP client that can simulate local request
     * @return MockHttpClient
     */
    protected function getHttpClient() : MockHttpClient
    {
        return DefaultMockHttpClient::create();
    }


    /**
     * If runInScope() should be forced
     * @return bool
     */
    protected static function isForceRunInScope() : bool
    {
        return false;
    }
}