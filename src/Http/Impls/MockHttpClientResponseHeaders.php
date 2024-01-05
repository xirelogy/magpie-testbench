<?php

namespace MagpieLib\TestBench\Http\Impls;

use Magpie\Facades\Http\HttpClientResponseHeaders;

/**
 * Headers for HTTP client response from mocked HTTP client
 * @internal
 */
class MockHttpClientResponseHeaders extends HttpClientResponseHeaders
{
    /**
     * Create and parse from given header lines
     * @param iterable<string> $headerLines
     * @return static
     */
    public static function parse(iterable $headerLines) : static
    {
        $ret = new static();

        foreach ($headerLines as $headerLine) {
            $headerElements = explode(':', $headerLine, 2);
            if (count($headerElements) < 2) continue;

            $ret->addHeader(trim($headerElements[0]), trim($headerElements[1]), true);
        }

        return $ret;
    }
}