<?php

namespace MagpieLib\TestBench\Http\Defaults;

use Magpie\Facades\Http\HttpClientResponseBody;
use MagpieLib\TestBench\Http\MockHttpClientResponse;

/**
 * Default implementation of MockHttpClientResponse
 */
class DefaultMockHttpClientResponse extends MockHttpClientResponse
{
    /**
     * Constructor
     * @param string $scheme
     * @param string $httpVersion
     * @param int $httpStatusCode
     * @param iterable<string> $headerLines
     * @param HttpClientResponseBody $body
     */
    public function __construct(string $scheme, string $httpVersion, int $httpStatusCode, iterable $headerLines, HttpClientResponseBody $body)
    {
        parent::__construct($scheme, $httpVersion, $httpStatusCode, $headerLines, $body);
    }
}