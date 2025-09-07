<?php

namespace MagpieLib\TestBench\Configurations\Prepares;

use Magpie\Logs\Loggers\DummyLogger;
use Magpie\Models\Commands\Features\DatabaseCommandFeature;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentPreparable;
use MagpieLib\TestBench\Configurations\TestEnvironmentContext;

/**
 * Synchronize the database schema in preparation for test
 */
class DatabaseSchemaSynced implements TestEnvironmentPreparable
{
    /**
     * @var array<string> All paths
     */
    protected readonly array $paths;
    /**
     * @var bool If prepare will log
     */
    protected readonly bool $isLog;


    /**
     * Constructor
     * @param iterable<string> $paths
     * @param bool $isLog
     */
    protected function __construct(iterable $paths, bool $isLog)
    {
        $this->paths = iter_flatten($paths);
        $this->isLog = $isLog;
    }


    /**
     * @inheritDoc
     */
    public function prepare(TestEnvironmentContext $context) : void
    {
        $logger = $this->isLog ? $context->getLogger() : new DummyLogger();
        $listener = DatabaseCommandFeature::createSyncSchemaListener($logger);
        DatabaseCommandFeature::syncSchema($listener, $this->paths);
    }


    /**
     * Create an instance
     * @param iterable<string> $paths
     * @param bool $isLog
     * @return static
     */
    public static function create(iterable $paths, bool $isLog = true) : static
    {
        return new static($paths, $isLog);
    }
}