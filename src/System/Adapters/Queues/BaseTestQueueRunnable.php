<?php

namespace MagpieLib\TestBench\System\Adapters\Queues;

use Exception;
use Magpie\Facades\Random;
use Magpie\General\Randoms\RandomCharset;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Logs\LogRelay;
use Magpie\Logs\Relays\SpecificFileLogRelay;
use Magpie\Queues\BaseQueueRunnable;
use Magpie\System\Kernel\Kernel;
use MagpieLib\TestBench\System\Adapters\Impls\TestEnvironmentExported;
use MagpieLib\TestBench\System\Adapters\Impls\TestEnvironmentHost;

/**
 * Extension of BaseQueueRunnable to support running in test environment
 */
abstract class BaseTestQueueRunnable extends BaseQueueRunnable
{
    /**
     * @var TestEnvironmentExported|null The exported test environment host
     */
    private readonly ?TestEnvironmentExported $testEnv;
    /**
     * @var string|int|null Current identity
     */
    private string|int|null $ident;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->testEnv = TestEnvironmentHost::instance()?->export();
        $this->ident = null;
    }


    /**
     * Corresponding identity, if running
     * @return string|int|null
     */
    public function getIdent() : string|int|null
    {
        return $this->ident;
    }


    /**
     * @inheritDoc
     */
    protected final function onRun() : void
    {
        try {
            // Reinitialize the test environment
            if ($this->testEnv !== null) {
                TestEnvironmentHost::reinitialize($this->testEnv);
            }

            // Setup logging
            $relay = $this->createDefaultLogRelay();
            $logger = new DefaultLogger($relay);
            Kernel::current()->setLogger($logger);

            $this->onRunInTest();
        } finally {
            $this->ident = null;
        }
    }


    /**
     * Create instance of default logger
     * @return LogRelay
     */
    protected function createDefaultLogRelay() : LogRelay
    {
        $ident = @getmypid();
        if ($ident === false) $ident = Random::string(8, RandomCharset::LOWER_ALPHANUM);
        $this->ident = $ident;

        $filename = $this->createDefaultLoggerFilename($ident);
        return new SpecificFileLogRelay($filename, Kernel::current()->getConfig()->createDefaultLogConfig());
    }


    /**
     * Create the filename for the default logger, assuming the default logger to be a specific file logger
     * @param string|int $ident
     * @return string
     */
    protected function createDefaultLoggerFilename(string|int $ident) : string
    {
        return "process.$ident.log";
    }


    /**
     * Actual running (in test environment)
     * @return void
     * @throws Exception
     */
    protected abstract function onRunInTest() : void;
}