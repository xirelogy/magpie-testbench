<?php

namespace MagpieLib\TestBench\Configurations\Impls;

use Magpie\Logs\Concepts\Loggable;
use MagpieLib\TestBench\Configurations\TestEnvironmentConfiguration;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

/**
 * Actual implementation for the test environment context
 * @internal
 */
final class ActualTestEnvironmentContext extends TestEnvironmentContext
{
    /**
     * @var TestEnvironmentConfiguration Associated test configuration
     */
    protected readonly TestEnvironmentConfiguration $testConfig;
    /**
     * @var Loggable Logging instance
     */
    protected Loggable $logger;


    /**
     * Constructor
     * @param TestEnvironmentConfiguration $testConfig
     * @param Loggable $logger
     */
    public function __construct(TestEnvironmentConfiguration $testConfig, Loggable $logger)
    {
        $this->testConfig = $testConfig;
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function getArtifactsPathOf(string $path = '') : string
    {
        $ret = $this->testConfig->getProjectRelativeArtifactsPath();
        while (str_ends_with($ret, '/')) {
            $ret = substr($ret, -1);
        }

        if (!empty($path)) {
            if (!str_starts_with($path, '/')) $path = "/$path";
        }

        return "$ret$path";
    }


    /**
     * @inheritDoc
     */
    public function getLogger() : Loggable
    {
        return $this->logger;
    }
}