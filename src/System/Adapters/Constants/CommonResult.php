<?php

namespace MagpieLib\TestBench\System\Adapters\Constants;

use Magpie\Consoles\DisplayStyle;
use Magpie\General\Factories\Annotations\NamedLabel;
use Magpie\General\Factories\Annotations\NamedPayload;
use Magpie\General\Factories\Annotations\NamedString;

/**
 * Commonly result for statistic
 */
enum CommonResult : string
{
    /**
     * Test passed
     */
    #[NamedString]
    #[NamedLabel('passed')]
    #[NamedPayload(DisplayStyle::INFO)]
    case PASSED = 'passed';

    /**
     * Test failed
     */
    #[NamedString]
    #[NamedLabel('failed')]
    #[NamedPayload(ExtraConsoleStyle::SIMPLE_ERROR)]
    case FAILED = 'failed';

    /**
     * Risky test case
     */
    #[NamedString]
    #[NamedLabel('risky')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case RISKY = 'risky';

    /**
     * Incomplete test case
     */
    #[NamedString]
    #[NamedLabel('incomplete')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case INCOMPLETE = 'incomplete';

    /**
     * Skipped test
     */
    #[NamedString]
    #[NamedLabel('skipped')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case SKIPPED = 'skipped';

    /**
     * Deprecation triggered during test
     */
    #[NamedString]
    #[NamedLabel('deprecated')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case DEPRECATED = 'deprecated';

    /**
     * Warning triggered during test
     */
    #[NamedString]
    #[NamedLabel('warning')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case WARNING = 'warning';

    /**
     * Notice triggered during test
     */
    #[NamedString]
    #[NamedLabel('notice')]
    #[NamedPayload(DisplayStyle::WARNING)]
    case NOTICE = 'notice';
}