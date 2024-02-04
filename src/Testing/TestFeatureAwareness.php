<?php

namespace MagpieLib\TestBench\Testing;

use Magpie\General\Traits\StaticClass;
use MagpieLib\TestBench\System\Adapters\Impls\TestEnvironmentHost;

/**
 * Test environment feature awareness test
 */
final class TestFeatureAwareness
{
    use StaticClass;


    /**
     * If test is running
     * @return bool
     */
    public static function isTesting() : bool
    {
        return TestEnvironmentHost::instance() !== null;
    }


    /**
     * If the artifacts directory is ready
     * @return bool
     */
    public static function hasArtifactsDirectory() : bool
    {
        return TestEnvironmentHost::instance()?->hasArtifactsDirectory() ?? false;
    }


    /**
     * The relative path in relative to the artifacts root
     * @param string $path
     * @return string
     */
    public static function getArtifactsPathOf(string $path = '') : string
    {
        return TestEnvironmentHost::instance()?->getArtifactsPathOf($path) ?? $path;
    }
}
