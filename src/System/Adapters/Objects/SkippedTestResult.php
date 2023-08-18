<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\Skipped as PhpUnitEventTestSkipped;

/**
 * A 'skipped' test result
 */
class SkippedTestResult extends MessagedTestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'skipped';


    /**
     * Constructor
     * @param PhpUnitEventTestSkipped $event
     */
    protected function __construct(PhpUnitEventTestSkipped $event)
    {
        parent::__construct($event, $event->message());
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return '-';
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
        return CommonResult::SKIPPED;
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
     * @param PhpUnitEventTestSkipped $event
     * @return static
     */
    public static function create(PhpUnitEventTestSkipped $event) : static
    {
        return new static($event);
    }
}