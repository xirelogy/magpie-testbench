<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\General\Packs\PackContext;
use PHPUnit\Event\Event as PhpUnitEvent;

/**
 * Test result with thrown exception (or failure representation)
 */
abstract class ThrownTestResult extends TestResult
{
    /**
     * @var ThrowableResult Exception representing the failure
     */
    public readonly ThrowableResult $exception;


    /**
     * Constructor
     * @param PhpUnitEvent $event
     * @param ThrowableResult $exception
     */
    protected function __construct(PhpUnitEvent $event, ThrowableResult $exception)
    {
        parent::__construct($event);

        $this->exception = $exception;
    }


    /**
     * @inheritDoc
     */
    public function getMessage() : string
    {
        return $this->exception->message;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->exception = $this->exception;
    }
}