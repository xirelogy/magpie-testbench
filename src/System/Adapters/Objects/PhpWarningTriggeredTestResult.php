<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\PhpWarningTriggered as PhpUnitEventTestPhpWarningTriggered;

/**
 * Test result from PHP native warning triggered
 */
class PhpWarningTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'php-warning';


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
     * @param PhpUnitEventTestPhpWarningTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestPhpWarningTriggered $event) : static
    {
        $location = new CodeLocation($event->file(), $event->line());
        return new static($event, $event->message(), $location);
    }
}