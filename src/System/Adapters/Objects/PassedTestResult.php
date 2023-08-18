<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Consoles\DisplayStyle;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;
use MagpieLib\TestBench\System\Adapters\Constants\CommonSymbol;
use PHPUnit\Event\Test\Finished as PhpUnitEventTestFinished;
use PHPUnit\Event\Test\Passed as PhpUnitEventTestPassed;

/**
 * A 'passed' test result
 */
class PassedTestResult extends TestResult
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'passed';


    /**
     * @inheritDoc
     */
    public function getSymbol() : string
    {
        return CommonSymbol::SUCCESS;
    }


    /**
     * @inheritDoc
     */
    public function getSymbolDisplayStyle() : DisplayStyle|string|null
    {
        return DisplayStyle::INFO;
    }


    /**
     * @inheritDoc
     */
    protected function getStatisticResultType() : CommonResult
    {
        return CommonResult::PASSED;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Construct from given event
     * @param PhpUnitEventTestPassed|PhpUnitEventTestFinished $event
     * @return static
     */
    public static function create(PhpUnitEventTestPassed|PhpUnitEventTestFinished $event) : static
    {
        return new static($event);
    }
}