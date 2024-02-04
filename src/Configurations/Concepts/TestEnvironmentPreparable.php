<?php

namespace MagpieLib\TestBench\Configurations\Concepts;

use Exception;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

/**
 * May prepare the test environment
 */
interface TestEnvironmentPreparable
{
    /**
     * Prepare the test environment
     * @param TestEnvironmentContext $context
     * @return void
     * @throws Exception
     */
    public function prepare(TestEnvironmentContext $context) : void;
}