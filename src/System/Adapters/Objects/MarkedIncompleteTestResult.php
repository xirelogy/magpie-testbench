<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\MarkedIncomplete as PhpUnitEventTestMarkedIncomplete;

/**
 * A 'marked incomplete' test result
 */
class MarkedIncompleteTestResult extends ThrownTestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'marked-incomplete';


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return '?';
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
        return CommonResult::INCOMPLETE;
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
     * @param PhpUnitEventTestMarkedIncomplete $event
     * @return static
     */
    public static function create(PhpUnitEventTestMarkedIncomplete $event) : static
    {
        $exception = ThrowableResult::create($event->throwable());
        return new static($event, $exception);
    }
}