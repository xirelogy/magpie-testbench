<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use MagpieLib\TestBench\System\Adapters\Constants\CommonSymbol;
use PHPUnit\Event\Test\ConsideredRisky as PhpUnitEventTestConsideredRisky;

/**
 * A 'risky' test result
 */
class RiskyTestResult extends MessagedTestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'risky';


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return CommonSymbol::WARNING;
    }


    /**
     * @inheritDoc
     */
    public function getSymbolDisplayStyle() : DisplayStyle|string|null
    {
        return DisplayStyle::WARNING;
    }


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::RISKY;
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
     * @param PhpUnitEventTestConsideredRisky $event
     * @return static
     */
    public static function create(PhpUnitEventTestConsideredRisky $event) : static
    {
        return new static($event, $event->message());
    }
}