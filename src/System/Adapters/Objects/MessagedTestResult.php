<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use PHPUnit\Event\Event as PhpUnitEvent;

/**
 * Test result with message
 */
abstract class MessagedTestResult extends TestResult
{
    /**
     * @var string Response message / error message
     */
    public readonly string $message;


    /**
     * Constructor
     * @param PhpUnitEvent $event
     * @param string $message
     */
    protected function __construct(PhpUnitEvent $event, string $message)
    {
        parent::__construct($event);

        $this->message = $message;
    }


    /**
     * @inheritDoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->message = $this->message;
    }
}