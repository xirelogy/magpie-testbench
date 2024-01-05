<?php

namespace MagpieLib\TestBench\Http\Defaults;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpClientRequestOption;
use MagpieLib\TestBench\Http\MockHttpClientPendingRequest;
use MagpieLib\TestBench\Http\MockHttpClientResponseListener;

/**
 * Default implementation of MockHttpClientPendingRequest
 */
class DefaultMockHttpClientPendingRequest extends MockHttpClientPendingRequest
{
    /**
     * Constructor
     * @param string $method
     * @param string $hostname
     * @param string $path
     * @param array<string, mixed> $parentHeaders
     * @param array<HttpClientRequestOption> $parentOptions
     * @throws SafetyCommonException
     */
    public function __construct(string $method, string $hostname, string $path, array $parentHeaders, array $parentOptions)
    {
        parent::__construct($method, $hostname, $path, $parentHeaders, $parentOptions);
    }


    /**
     * @inheritDoc
     */
    protected function createRenderListener() : MockHttpClientResponseListener
    {
        return new DefaultMockHttpClientResponseListener();
    }
}