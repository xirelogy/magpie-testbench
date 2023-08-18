<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\PhpDeprecationTriggered as PhpUnitEventTestPhpDeprecationTriggered;

/**
 * Test result from PHP native deprecation triggered
 */
class PhpDeprecationTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'php-deprecation';


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
     * @param PhpUnitEventTestPhpDeprecationTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestPhpDeprecationTriggered $event) : static
    {
        $location = new CodeLocation($event->file(), $event->line());
        return new static($event, $event->message(), $location);
    }
}