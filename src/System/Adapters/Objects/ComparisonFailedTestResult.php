<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use PHPUnit\Event\Code\ComparisonFailure as PhpUnitEventComparisonFailure;
use PHPUnit\Event\Test\Failed as PhpUnitEventTestFailed;

/**
 * A 'failed' test result caused by comparison failure
 */
class ComparisonFailedTestResult extends FailedTestResult
{
    /**
     * Constructor
     * @param PhpUnitEventTestFailed $event
     * @param PhpUnitEventComparisonFailure $comparisonFailure
     */
    protected function __construct(PhpUnitEventTestFailed $event, PhpUnitEventComparisonFailure $comparisonFailure)
    {
        parent::__construct($event);

        _used($comparisonFailure);
    }


    /**
     * Create specific from given event
     * @param PhpUnitEventTestFailed $event
     * @param PhpUnitEventComparisonFailure $comparisonFailure
     * @return static
     * @internal
     */
    public static function _createSpecific(PhpUnitEventTestFailed $event, PhpUnitEventComparisonFailure $comparisonFailure) : static
    {
        return new static($event, $comparisonFailure);
    }
}