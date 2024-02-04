<?php

namespace MagpieLib\TestBench\Configurations\Concepts;

use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

/**
 * May release the prepared item from the test environment
 */
interface TestEnvironmentReleasable
{
    /**
     * Release the prepared item for the test environment
     * @param TestEnvironmentContext $context
     * @return void
     */
    public function release(TestEnvironmentContext $context) : void;
}