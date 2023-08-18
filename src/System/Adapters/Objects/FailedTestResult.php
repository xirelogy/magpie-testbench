<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use MagpieLib\TestBench\System\Adapters\Constants\CommonSymbol;
use MagpieLib\TestBench\System\Adapters\Constants\ExtraConsoleStyle;
use PHPUnit\Event\Test\Failed as PhpUnitEventTestFailed;

/**
 * A 'failed' test result
 */
class FailedTestResult extends ThrownTestResult
{
    public const TYPECLASS = 'failed';

    /**
     * Constructor
     * @param PhpUnitEventTestFailed $event
     */
    protected function __construct(PhpUnitEventTestFailed $event)
    {
        $exception = ThrowableResult::create($event->throwable());
        parent::__construct($event, $exception);
    }


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
     * @param PhpUnitEventTestFailed $event
     * @return static
     */
    public static function create(PhpUnitEventTestFailed $event) : static
    {
        if ($event->hasComparisonFailure()) {
            return ComparisonFailedTestResult::_createSpecific($event, $event->comparisonFailure());
        }

        return new self($event);
    }
}