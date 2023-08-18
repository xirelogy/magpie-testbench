<?php

namespace MagpieLib\TestBench\System\Adapters\Objects;

use Magpie\Objects\CommonObject;
use MagpieLib\TestBench\System\Adapters\Constants\CommonResult;

/**
 * Test statistic
 */
abstract class TestStatistic extends CommonObject
{
    /**
     * @var array<string, int> All result counters
     */
    protected array $resultCounters = [];


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resultCounters[CommonResult::PASSED->value] = 0;
        $this->resultCounters[CommonResult::FAILED->value] = 0;
    }


    /**
     * Add a result
     * @param CommonResult $resultType
     * @param int $count
     * @return void
     */
    public function addResult(CommonResult $resultType, int $count = 1) : void
    {
        if ($count < 1) return; // Anything below 1 is not considered

        $resultTypeKey = $resultType->value;
        $count = $this->resultCounters[$resultTypeKey] ?? 0;
        ++$count;
        $this->resultCounters[$resultTypeKey] = $count;
    }
}