<?php

namespace MagpieLib\TestBench\Configurations\Concepts;

use MagpieLib\TestBench\Configurations\TestEnvironmentConfiguration;

/**
 * May configure the test environment through application configuration
 */
interface TestAppConfigurable
{
    /**
     * Configure the test environment
     * @return TestEnvironmentConfiguration
     */
    public function configureTestEnvironment() : TestEnvironmentConfiguration;
}
