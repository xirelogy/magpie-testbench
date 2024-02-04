<?php

namespace MagpieLib\TestBench\Configurations\Prepares;

use Magpie\Facades\FileSystem\Providers\Local\LocalFileSystem;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;
use MagpieLib\TestBench\System\Adapters\Impls\TestEnvironmentHost;

/**
 * Prepare and make sure the artifacts directory is ready
 */
class ArtifactsDirectoryPrepared implements TestEnvironmentPreparable
{
    /**
     * @var bool If the artifacts directory will be emptied
     */
    protected readonly bool $isEmpty;


    /**
     * Constructor
     * @param bool $isEmpty
     */
    protected function __construct(bool $isEmpty)
    {
        $this->isEmpty = $isEmpty;
    }


    /**
     * @inheritDoc
     */
    public function prepare(TestEnvironmentContext $context) : void
    {
        $path = $context->getArtifactsPathOf();

        $fs = LocalFileSystem::initializeFromProjectDir();

        if ($this->isEmpty) $fs->deleteDirectory($path);

        $fs->createDirectory($path);

        // Artifacts directory is now ready
        TestEnvironmentHost::instance()?->markArtifactsDirectoryReady();
    }


    /**
     * Create an instance
     * @param bool $isEmpty
     * @return static
     */
    public static function create(bool $isEmpty = true) : static
    {
        return new static($isEmpty);
    }
}