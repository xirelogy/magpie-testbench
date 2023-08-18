<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use Magpie\General\Packs\PackContext;
use MagpieLib\TestBench\System\Adapters\Constants\CommonSymbol;

/**
 * Test result from triggers
 */
abstract class TriggeredTestResult extends LocatedMessagedTestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'triggered';


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return CommonSymbol::WARNING;
    }


    /**
     * @inheritDoc
     */
    public function getSymbolDisplayStyle() : DisplayStyle|string|null
    {
        return DisplayStyle::WARNING;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->triggerTypeClass = static::getTriggerTypeClass();
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Trigger type class
     * @return string
     */
    public static abstract function getTriggerTypeClass() : string;
}