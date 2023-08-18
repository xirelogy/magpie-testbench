<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Test\PhpNoticeTriggered as PhpUnitEventTestPhpNoticeTriggered;

/**
 * Test result from PHP native notice triggered
 */
class PhpNoticeTriggeredTestResult extends TriggeredTestResult
{
    /**
     * Current trigger type class
     */
    public const TRIGGER_TYPECLASS = 'php-notice';


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::NOTICE;
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
     * @param PhpUnitEventTestPhpNoticeTriggered $event
     * @return static
     */
    public static function create(PhpUnitEventTestPhpNoticeTriggered $event) : static
    {
        $location = new CodeLocation($event->file(), $event->line());
        return new static($event, $event->message(), $location);
    }
}