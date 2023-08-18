<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\PhpunitWarningTriggered as PhpUnitEventTestPhpunitWarningTriggered;

/**
 * Test result from PHP-unit warning triggered
 */
class PhpunitWarningTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'phpunit-warning';


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
     * @param PhpUnitEventTestPhpunitWarningTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestPhpunitWarningTriggered $event) : static
    {
        $location = CodeLocation::unknown();
        return new static($event, $event->message(), $location);
    }
}