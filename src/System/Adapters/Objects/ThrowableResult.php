<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use Magpie\General\Str;
use Magpie\General\Sugars\Excepts;
use Magpie\Objects\CommonObject;
use PHPUnit\Event\Code\Throwable as PhpUnitEventThrowable;

/**
 * Throwable processed for result
 */
class ThrowableResult extends CommonObject
{
    /**
     * @var string Error class
     */
    public readonly string $className;
    /**
     * @var string Error message
     */
    public readonly string $message;
    /**
     * @var string Error description
     */
    public readonly string $description;
    /**
     * @var array<string> Exception stack trace
     */
    public readonly array $stackTrace;
    /**
     * @var static|null Previous throwable, if any
     */
    public readonly ?self $previous;


    /**
     * Constructor
     * @param PhpUnitEventThrowable $exception
     */
    protected function __construct(PhpUnitEventThrowable $exception)
    {
        $this->className = $exception->className();
        $this->message = $exception->message();
        $this->description = $exception->description();
        $this->stackTrace = iter_flatten(static::parseStackTrace($exception->stackTrace()), false);

        $this->previous = $exception->hasPrevious() ?
            Excepts::noThrow(fn () => static::create($exception->previous())) :
            null;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->message = $this->message;
        $ret->stackTrace = $this->stackTrace;
        $ret->previous = $this->previous;
    }


    /**
     * Construct from given exception
     * @param PhpUnitEventThrowable $exception
     * @return static
     */
    public static function create(PhpUnitEventThrowable $exception) : static
    {
        return new static($exception);
    }


    /**
     * Parse given stack trace
     * @param string $trace
     * @return iterable<CodeLocation|string>
     */
    protected static function parseStackTrace(string $trace) : iterable
    {
        foreach (explode("\n", $trace) as $line) {
            if (Str::isNullOrEmpty($line)) continue;

            yield static::parseStackTraceLine($line);
        }
    }


    /**
     * Parse given stack trace line
     * @param string $line
     * @return CodeLocation|string
     */
    protected static function parseStackTraceLine(string $line) : CodeLocation|string
    {
        $location = Excepts::noThrow(fn () => CodeLocation::parseLine($line));
        if ($location !== null) return $location;

        return $line;
    }
}