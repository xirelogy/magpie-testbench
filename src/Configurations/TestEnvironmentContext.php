<?php

namespace MagpieLib\TestBench\Configurations;

use Magpie\Logs\Concepts\Loggable;

/**
 * Test environment context
 */
abstract class TestEnvironmentContext
{
    /**
     * The relative path in relative to the artifacts root
     * @param string $path
     * @return string
     */
    public abstract function getArtifactsPathOf(string $path = '') : string;


    /**
     * Logging target during environment execution
     * @return Loggable
     */
    public abstract function getLogger() : Loggable;
}