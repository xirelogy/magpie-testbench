<?php

namespace MagpieLib\TestBench\System\Adapters\Impls;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\Env;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\ConsoleCustomization;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;
use Magpie\HttpServer\ServerCollection;
use Magpie\Locales\I18n;
use Magpie\Objects\NumericVersion;
use Magpie\System\Kernel\Kernel;
use MagpieLib\TestBench\System\Adapters\Constants\ExtraConsoleStyle;
use MagpieLib\TestBench\System\Adapters\Impls\Subscribers\PhpUnitStartedSubscriber;
use PHPUnit\Runner\Version as PhpUnitVersion;

/**
 * PHPUnit related configuration
 * @internal
 */
class PhpUnitConfig
{
    use StaticClass;

    /**
     * Environment name that needs to be registered when running
     */
    public const ENV_NAME_RUNNING = 'MAGPIE_TESTBENCH_RUNNING';
    /**
     * Environment name for project root path
     */
    public const ENV_NAME_ROOT = 'MAGPIE_TESTBENCH_ROOT';
    /**
     * Environment name for printer instance to be used
     */
    public const ENV_NAME_PRINTER = 'MAGPIE_TESTBENCH_PRINTER';

    /**
     * @var string|null Registered root path
     */
    public static ?string $rootPath = null;


    /**
     * Autoload from PHPUnit
     * @return void
     */
    public static function autoloadFromPhpUnit() : void
    {
        // Do not proceed without the server variable
        $envRunning = $_SERVER[static::ENV_NAME_RUNNING] ?? null;
        if ($envRunning !== 'true') return;

        // Register root path if found
        $rootPath = $_SERVER[static::ENV_NAME_ROOT] ?? null;
        if ($rootPath !== null) static::$rootPath = $rootPath;

        Excepts::noThrow(function () {
            static::autoloadProject();
        });
    }


    /**
     * Ensure autoload is completed in relation to the project
     * @return void
     */
    protected static function autoloadProject() : void
    {
        if (static::$rootPath === null) return;

        $autoloadFilename = @realpath(static::$rootPath . '/vendor/autoload.php');
        if ($autoloadFilename === false) return;
        if (!file_exists($autoloadFilename)) return;
        if (!is_file($autoloadFilename)) return;

        require $autoloadFilename;
    }


    /**
     * Boot up from PHPUnit
     * @return void
     */
    public static function bootFromPhpUnit() : void
    {
        // Do not proceed without the server variable
        $envRunning = $_SERVER[static::ENV_NAME_RUNNING] ?? null;
        if ($envRunning !== 'true') return;

        Excepts::noThrow(function () {
            static::bootMagpie();
            static::ensurePhpUnitSupported();

            PhpUnitStartedSubscriber::register();
        });
    }


    /**
     * Ensure that magpie kernel is booted up
     * @return void
     */
    protected static function bootMagpie() : void
    {
        if (Kernel::hasCurrent()) return;

        // Locate the configuration
        $bootConfigPath = static::findBootConfig();
        if ($bootConfigPath === null) {
            static::lowLevelWarning('Cannot resolve boot config file');
            return;
        }
        $config = require_once $bootConfigPath;

        // Determine the actual project root path and boot up the kernel
        $projectPath = dirname($bootConfigPath, 2);
        Env::usingEnv('.env.testing');
        $kernel = Kernel::boot($projectPath, $config);

        // Handle LANG environment variable
        $serverVars = ServerCollection::capture();
        $lang = $serverVars->safeOptional('LANG', StringParser::createTrimEmptyAsNull());
        if ($lang !== null) I18n::setCurrentLocale($lang);

        // Allow customization of the default console
        $customFn = function (ConsoleCustomization $custom) {
            $custom->defineManualConsoleStyle(ExtraConsoleStyle::SIMPLE_ERROR, 'red');
        };

        // Register the default console
        $kernel->registerProvider(Consolable::class, $kernel->getConfig()->createDefaultConsolable($customFn));
    }


    /**
     * Find boot config
     * @return string|null
     */
    protected static function findBootConfig() : ?string
    {
        // If project directory is provided, use the project directory
        if (static::$rootPath !== null) {
            $projectBootPath = static::$rootPath . '/boot/test-config.php';
            if (file_exists($projectBootPath) && is_file($projectBootPath)) return $projectBootPath;

            $projectBootPath = static::$rootPath . '/boot/config.php';
            if (file_exists($projectBootPath) && is_file($projectBootPath)) return $projectBootPath;

            // Do not look further
            return null;
        }

        // Fallback by looking upwards
        $currentDir = realpath(__DIR__);
        while (true) {
            $currentDir = dirname($currentDir);

            $currentPath = $currentDir . '/boot/test-config.php';
            if (file_exists($currentPath) && is_file($currentPath)) return $currentPath;

            $currentPath = $currentDir . '/boot/config.php';
            if (file_exists($currentPath) && is_file($currentPath)) return $currentPath;

            if ($currentDir === '' || $currentDir === '/') return null;
        }
    }


    /**
     * Ensure that PHPUnit is supported
     * @return int
     * @throws SafetyCommonException
     */
    public static function ensurePhpUnitSupported() : int
    {
        $majorVersion = static::getPhpUnitMajorVersion();

        if ($majorVersion < 10) {
            throw new UnsupportedException(_l('PHPUnit 10.x and above is required'));
        }

        return $majorVersion;
    }


    /**
     * Get the PHPUnit major version
     * @return int
     * @throws SafetyCommonException
     */
    protected static function getPhpUnitMajorVersion() : int
    {
        $phpUnitVersion = NumericVersion::parse(PhpUnitVersion::id());
        return $phpUnitVersion->getMajor();
    }


    /**
     * Write low-level warning messages (assuming no proper context is available)
     * @param string $message
     * @return void
     */
    protected static function lowLevelWarning(string $message) : void
    {
        $message = str_replace("\r", "", $message);
        $messageLines = explode("\n", $message);
        foreach ($messageLines as $messageLine) {
            echo "WARNING! $messageLine\n";
        }
    }
}