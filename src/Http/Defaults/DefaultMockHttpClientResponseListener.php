<?php

namespace MagpieLib\TestBench\Http\Defaults;

use Magpie\Facades\Http\HttpClientResponseBody;
use MagpieLib\TestBench\Http\MockHttpClientResponse;
use MagpieLib\TestBench\Http\MockHttpClientResponseListener;

/**
 * Default implementation of MockHttpClientResponseListener
 */
class DefaultMockHttpClientResponseListener extends MockHttpClientResponseListener
{
    /**
     * @inheritDoc
     */
    protected function createActualResponse(string $scheme, string $httpVersion, int $httpStatusCode, iterable $headerLines, HttpClientResponseBody $body) : MockHttpClientResponse
    {
        return new DefaultMockHttpClientResponse($scheme, $httpVersion, $httpStatusCode, $headerLines, $body);
    }
}