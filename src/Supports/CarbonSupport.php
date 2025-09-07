<?php

namespace MagpieLib\TestBench\Supports;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Traits\StaticClass;

/**
 * Date/time (Carbon library) support
 */
class CarbonSupport
{
    use StaticClass;


    /**
     * Parse incoming value into actual date/time data
     * @param CarbonInterface|string|int|float $value
     * @param string|null $defaultTimezone Default timezone when parsing string values
     * @return CarbonInterface
     */
    public static function parse(CarbonInterface|string|int|float $value, ?string $defaultTimezone = null) : CarbonInterface
    {
        $defaultTimezone = $defaultTimezone ?? SystemTimezone::default();

        if ($value instanceof CarbonInterface) return $value;
        if (is_int($value) || is_float($value)) return Carbon::createFromTimestamp($value);

        return Carbon::parse($value, $defaultTimezone);
    }


    /**
     * Parse incoming value into actual immutable date/time data
     * @param CarbonInterface|string|int|float $value
     * @param string|null $defaultTimezone Default timezone when parsing string values
     * @return CarbonImmutable
     */
    public static function parseImmutable(CarbonInterface|string|int|float $value, ?string $defaultTimezone = null) : CarbonImmutable
    {
        return static::parse($value, $defaultTimezone)->toImmutable();
    }
}