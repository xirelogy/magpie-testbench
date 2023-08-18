<?php

namespace MagpieLib\TestBench\Impls\Adapters;

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
use MagpieLib\TestBench\Impls\Adapters\Subscribers\PhpUnitStartedSubscriber;
use MagpieLib\TestBench\System\Adapters\Constants\ExtraConsoleStyle;
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
     * Environment name for printer instance to be used
     */
    public const ENV_NAME_PRINTER = 'MAGPIE_TESTBENCH_PRINTER';


    /**
     * Boot up from PHPUnit
     * @return void
     * @internal
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

        // Locate the configuration by moving upwards
        $bootConfigPath = static::findBootConfig();
        if ($bootConfigPath === null) return;
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
        $currentDir = realpath(__DIR__);
        while (true) {
            $currentDir = dirname($currentDir);
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
}