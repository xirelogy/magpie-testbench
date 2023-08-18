<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\WarningTriggered as PhpUnitEventTestWarningTriggered;

/**
 * Test result from warning triggered
 */
class WarningTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'warning';


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::WARNING;
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
     * @param PhpUnitEventTestWarningTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestWarningTriggered $event) : static
    {
        $location = new CodeLocation($event->file(), $event->line());
        return new static($event, $event->message(), $location);
    }
}