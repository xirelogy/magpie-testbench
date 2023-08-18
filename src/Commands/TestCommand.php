<?php

namespace MagpieLib\TestBench\Commands;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandOptionDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Exceptions\ParseFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Facades\Console;
use Magpie\Facades\FileSystem\Providers\Local\LocalFileSystem;
use Magpie\General\Sugars\Excepts;
use Magpie\Locales\I18n;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;
use MagpieLib\TestBench\Impls\Adapters\PhpUnitConfig;

#[CommandSignature('test {--config=} {--printer=} {--debug}')]
#[CommandDescriptionL('Run tests')]
#[CommandOptionDescriptionL('config', 'Specific test configuration (default: phpunit.xml)')]
#[CommandOptionDescriptionL('printer', 'Specific output printer driver to be used')]
#[CommandOptionDescriptionL('debug', 'Enable debug output')]
class TestCommand extends Command
{
    /**
     * Default configuration filename
     */
    protected const DEFAULT_CONFIG_FILENAME = 'phpunit.xml';


    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $phpUnitMajorVersion = PhpUnitConfig::ensurePhpUnitSupported();

        if ($phpUnitMajorVersion > 10) {
            Console::warning(_l('PHPUnit beyond version 10.x is not tested'));
        }

        // Configure arguments
        $arguments = [];
        $arguments[] = '--no-output';
        $arguments[] = '--do-not-cache-result';

        // Use the specific configuration
        $configFile = $request->options->optional('config', static::createPhpUnitConfigParser(), static::DEFAULT_CONFIG_FILENAME);
        $arguments[] = '--configuration=' . $configFile;

        if ($request->options->safeOptional('debug')) {
            $arguments[] = '--log-events-verbose-text=php://stdout';
        }

        // Configure environment variables
        $env = [
            PhpUnitConfig::ENV_NAME_RUNNING => 'true',
            'LANG' => I18n::getCurrentLocale(),
        ];

        // Cascade arguments into environment variables
        $optPrinter = $request->options->optional('printer');
        if ($optPrinter !== null) $env[PhpUnitConfig::ENV_NAME_PRINTER] = $optPrinter;

        // Configure the command line
        $cmd = ProcessCommandLine::fromPhp(static::getPhpUnitExecutable(), ...$arguments)
            ->withEnvironment($env)
            ;

        // Create the process
        $process = Process::fromCommandLine($cmd);

        // Try to enable TTY mode
        Excepts::noThrow(fn () => $process->withTty());

        $process->run();
    }


    /**
     * Get the target executable binary
     * @return string
     * @throws SafetyCommonException
     */
    protected static function getPhpUnitExecutable() : string
    {
        $fs = LocalFileSystem::initializeFromWorkDir();

        if ($fs->isFileExist('vendor/phpunit/phpunit/phpunit')) {
            return 'vendor/phpunit/phpunit/phpunit';
        }

        throw new UnsupportedException(_l('PHPUnit executable not found'));
    }


    /**
     * Parse for the PHPUnit configuration
     * @return Parser
     */
    protected static function createPhpUnitConfigParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $fs = LocalFileSystem::initializeFromWorkDir();

            if ($fs->isFileExist($value)) {
                return $value;
            }

            // Try to resolve for a directory having 'phpunit.xml'
            $otherValue = $value;
            if (!str_ends_with($otherValue, '/')) $otherValue .= '/';
            if ($fs->isFileExist($otherValue . static::DEFAULT_CONFIG_FILENAME)) {
                return $otherValue . static::DEFAULT_CONFIG_FILENAME;
            }

            throw new ParseFailedException(_l('configuration file not found'));
        });
    }

}