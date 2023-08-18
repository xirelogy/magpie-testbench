<?php

namespace MagpieLib\TestBench\System\Adapters\Constants;

use Magpie\General\Traits\StaticClass;

/**
 * Commonly used symbol
 */
class CommonSymbol
{
    use StaticClass;

    /**
     * Successful
     */
    public const SUCCESS = '✓';
    /**
     * Failed
     */
    public const FAILED = '×';
    /**
     * Warning that deserves attention
     */
    public const WARNING = '!';
}