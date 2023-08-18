<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use PHPUnit\Event\Event as PhpUnitEvent;

/**
 * Test result with message and code location
 */
abstract class LocatedMessagedTestResult extends MessagedTestResult
{
    /**
     * @var CodeLocation Code location that caused the test result
     */
    public readonly CodeLocation $location;


    /**
     * Constructor
     * @param PhpUnitEvent $event
     * @param string $message
     * @param CodeLocation $location
     */
    protected function __construct(PhpUnitEvent $event, string $message, CodeLocation $location)
    {
        parent::__construct($event, $message);

        $this->location = $location;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->location = $this->location;
    }
}