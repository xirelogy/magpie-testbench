<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use MagpieLib\TestBench\System\Adapters\Constants\CommonSymbol;
use MagpieLib\TestBench\System\Adapters\Constants\ExtraConsoleStyle;
use PHPUnit\Event\Test\Errored as PhpUnitEventTestErrored;

/**
 * An 'errored' test result
 */
class ErroredTestResult extends ThrownTestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'errored';


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return CommonSymbol::FAILED;
    }


    /**
     * @inheritDoc
     */
    public function getSymbolDisplayStyle() : DisplayStyle|string|null
    {
        return ExtraConsoleStyle::SIMPLE_ERROR;
    }


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::FAILED;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Construct from given event
     * @param PhpUnitEventTestErrored $event
     * @return static
     */
    public static function create(PhpUnitEventTestErrored $event) : static
    {
        $exception = ThrowableResult::create($event->throwable());
        return new static($event, $exception);
    }
}