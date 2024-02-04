<?php

namespace MagpieLib\TestBench\Configurations;

use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\Prepares\ArtifactsDirectoryPrepared;

/**
 * Configuration for the test environment
 */
abstract class TestEnvironmentConfiguration
{
    /**
     * The path to store test artifacts in relative to the defined project directory
     * @return string
     */
    public function getProjectRelativeArtifactsPath() : string
    {
        return '/storage/tests';
    }


    /**
     * All preparations for the test environment
     * @return iterable<TestEnvironmentPreparable>
     */
    public function getPreparations() : iterable
    {
        yield ArtifactsDirectoryPrepared::create();
    }
}