<?php

namespace MagpieLib\TestBench\System\Adapters\Impls;

use Magpie\Facades\Log;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\EasyFiberPromise;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;
use MagpieLib\TestBench\Configurations\Concepts\TestAppConfigurable;
use MagpieLib\TestBench\Configurations\Concepts\TestEnvironmentReleasable;
use MagpieLib\TestBench\Configurations\Impls\ActualTestEnvironmentContext;
use MagpieLib\TestBench\Configurations\TestEnvironmentConfiguration;
use Throwable;

/**
 * Internal hosting of the test environment
 * @internal
 */
final class TestEnvironmentHost implements Releasable
{
    use StaticClass;

    /**
     * @var TestEnvironmentHost|null The specific instance
     */
    protected static ?self $instance = null;

    /**
     * @var TestEnvironmentConfiguration Associated test configuration
     */
    protected readonly TestEnvironmentConfiguration $testConfig;
    /**
     * @var ActualTestEnvironmentContext Context to service the test environment
     */
    protected readonly ActualTestEnvironmentContext $context;
    /**
     * @var bool If already released
     */
    protected bool $isReleased = false;
    /**
     * @var array All resources that can be released upon the environment shutdown
     */
    protected array $releasables = [];
    /**
     * @var bool If artifacts directory exist
     */
    protected bool $hasArtifactsDirectory = false;


    /**
     * Constructor
     * @param TestEnvironmentConfiguration $testConfig
     */
    protected function __construct(TestEnvironmentConfiguration $testConfig)
    {
        $this->testConfig = $testConfig;
        $this->context = new ActualTestEnvironmentContext($testConfig, Log::split('env-prepare'));
    }


    /**
     * Run initialization
     * @return void
     */
    private function runInit() : void
    {
        $releasables = [];
        try {
            // Prepare the environment as instructed
            foreach ($this->testConfig->getPreparations() as $preparation) {
                $preparation->prepare($this->context);
                if ($preparation instanceof TestEnvironmentReleasable) $releasables[] = $preparation;
            }

            // The release order shall be the reverse of the preparation order
            $this->releasables = array_reverse($releasables);
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if ($this->isReleased) return;
        $this->isReleased = true;

        // Release in order
        foreach ( $this->releasables as $releasable) {
            $releasable->release($this->context);
        }
        $this->releasables = [];

        Excepts::noThrow(fn () => EasyFiberPromise::loop());
    }


    /**
     * Mark artifacts directory ready
     * @return void
     */
    public function markArtifactsDirectoryReady() : void
    {
        $this->hasArtifactsDirectory = true;
    }


    /**
     * If artifacts directory is ready
     * @return bool
     */
    public function hasArtifactsDirectory() : bool
    {
        return $this->hasArtifactsDirectory;
    }


    /**
     * The relative path in relative to the artifacts root
     * @param string $path
     * @return string
     */
    public function getArtifactsPathOf(string $path = '') : string
    {
        return $this->context->getArtifactsPathOf($path);
    }


    /**
     * Re-import exported environment
     * @param TestEnvironmentExported $exported
     * @return $this
     */
    private function import(TestEnvironmentExported $exported) : static
    {
        $this->hasArtifactsDirectory = $exported->hasArtifactsDirectory;
        return $this;
    }


    /**
     * Export the current host into environment
     * @return TestEnvironmentExported
     */
    public function export() : TestEnvironmentExported
    {
        return new TestEnvironmentExported($this->hasArtifactsDirectory);
    }


    /**
     * Initialize the instance
     * @param TestEnvironmentConfiguration|null $testConfig
     * @return static
     */
    public static function initialize(?TestEnvironmentConfiguration $testConfig = null) : static
    {
        if (static::$instance !== null) ExceptionHandler::systemCritical('Multiple initialization of test environment');

        $testConfig = $testConfig ?? static::getTestEnvironmentConfig();

        static::$instance = new static($testConfig);
        static::$instance->runInit();

        return static::$instance;
    }


    /**
     * Reinitialize the instance
     * @param TestEnvironmentExported $exported
     * @return static
     */
    public static function reinitialize(TestEnvironmentExported $exported) : static
    {
        if (static::$instance === null) {
            $testConfig = static::getTestEnvironmentConfig();
            static::$instance = new static($testConfig);
        }

        return static::$instance->import($exported);
    }


    /**
     * Access to the instance
     * @return static|null
     */
    public static function instance() : ?static
    {
        return static::$instance;
    }


    /**
     * Access to the test environment configuration
     * @return TestEnvironmentConfiguration
     */
    public static function getTestEnvironmentConfig() : TestEnvironmentConfiguration
    {
        // When available via app config, return from there
        $appConfig = Kernel::current()->getConfig();
        if ($appConfig instanceof TestAppConfigurable) return $appConfig->configureTestEnvironment();

        // Otherwise, provide the default version
        return new class extends TestEnvironmentConfiguration {

        };
    }
}