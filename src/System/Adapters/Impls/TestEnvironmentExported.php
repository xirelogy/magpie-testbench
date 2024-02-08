<?php

namespace MagpieLib\TestBench\System\Adapters\Impls;

/**
 * Exported information from TestEnvironmentHost
 * @internal
 */
class TestEnvironmentExported
{
    /**
     * @var bool If artifacts directory exist
     */
    public readonly bool $hasArtifactsDirectory;


    /**
     * Constructor
     * @param bool $hasArtifactsDirectory
     */
    public function __construct(bool $hasArtifactsDirectory)
    {
        $this->hasArtifactsDirectory = $hasArtifactsDirectory;
    }
}