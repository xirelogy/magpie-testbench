<?php

namespace MagpieLib\TestBench\Http\Defaults;

use Magpie\General\Traits\StaticCreatable;
use MagpieLib\TestBench\Http\MockHttpClient;
use MagpieLib\TestBench\Http\MockHttpClientPendingRequest;

/**
 * Default implementation of MockHttpClient
 */
class DefaultMockHttpClient extends MockHttpClient
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    protected function createPendingRequest(string $method, string $hostname, string $path) : MockHttpClientPendingRequest
    {
        return new DefaultMockHttpClientPendingRequest($method, $hostname, $path, $this->headers, $this->options, $this->useLogger);
    }
}