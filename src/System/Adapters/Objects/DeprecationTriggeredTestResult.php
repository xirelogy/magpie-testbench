<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\DeprecationTriggered as PhpUnitEventTestDeprecationTriggered;

/**
 * Test result from deprecation triggered
 */
class DeprecationTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'deprecation';


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::DEPRECATED;
    }


    /**
     * @inheritDoc
     */
    public static function getTriggerTypeClass() : string
    {
        return static::TRIGGER_TYPECLASS;
    }


    /**
     * Construct from given event
     * @param PhpUnitEventTestDeprecationTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestDeprecationTriggered $event) : static
    {
        $location = new CodeLocation($event->file(), $event->line());
        return new static($event, $event->message(), $location);
    }
}