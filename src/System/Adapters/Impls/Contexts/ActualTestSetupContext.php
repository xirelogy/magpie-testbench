<?php

namespace MagpieLib\TestBench\System\Adapters\Impls\Contexts;

use Magpie\Facades\Console;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\LogConfig;
use MagpieLib\TestBench\Setups\TestSetupContext;

/**
 * Actual test setup context
 * @internal
 * FIXME DEPRECATED
 * @deprecated
 */
class ActualTestSetupContext extends TestSetupContext
{
    /**
     * @var Loggable Logger instance
     */
    protected readonly Loggable $logger;
    /**
     * @var string|null Artifacts path (in relative to working directory)
     */
    protected ?string $artifactsPath = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        $logConfig = new LogConfig(
            SystemTimezone::default(),
            LogConfig::DEFAULT_TIME_FORMAT,
            'test-setup',
        );

        $this->logger = Console::asLogger(logConfig: $logConfig);
    }


    /**
     * Register artifacts path
     * @param string $path
     * @return void
     * @internal
     */
    public function _registerArtifactsPath(string $path) : void
    {
        $this->artifactsPath = $path;
    }


    /**
     * @inheritDoc
     */
    public function getArtifactsPath() : ?string
    {
        return $this->artifactsPath;
    }


    /**
     * @inheritDoc
     */
    public function getLogger() : Loggable
    {
        return $this->logger;
    }
}