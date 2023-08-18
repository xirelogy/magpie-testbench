<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use PHPUnit\Event\Event as PhpUnitEvent;

/**
 * Test result
 */
abstract class TestResult extends TestObject implements TypeClassable
{
    use CommonPackable;

    /**
     * Constructor
     * @param PhpUnitEvent $event
     */
    protected function __construct(PhpUnitEvent $event)
    {
        _used($event);  // TODO: extract telemetryInfo
    }


    /**
     * Update statistics into the target statistic collector
     * @param TestStatistic $collector
     * @return void
     */
    public final function updateStatistic(TestStatistic $collector) : void
    {
        $type = $this->getStatisticResultType();
        $collector->addResult($type);
    }


    /**
     * The single character symbol representing current test result
     * @return string
     */
    public abstract function getSymbol() : string;


    /**
     * Associated display style for the symbol representing current test result
     * @return DisplayStyle|string|null
     */
    public function getSymbolDisplayStyle() : DisplayStyle|string|null
    {
        return null;
    }


    /**
     * The result type for statistic purpose
     * @return CommonResult
     */
    protected abstract function getStatisticResultType() : CommonResult;


    /**
     * Associated message describing the test result (if any)
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->typeClass = static::getTypeClass();
    }
}