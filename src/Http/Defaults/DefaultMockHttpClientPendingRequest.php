<?php

namespace MagpieLib\TestBench\Http\Defaults;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\Logs\Concepts\Loggable;
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
     * @param Loggable|null $logger
     * @throws SafetyCommonException
     */
    public function __construct(string $method, string $hostname, string $path, array $parentHeaders, array $parentOptions, ?Loggable $logger)
    {
        parent::__construct($method, $hostname, $path, $parentHeaders, $parentOptions, $logger);
    }


    /**
     * @inheritDoc
     */
    protected function createRenderListener() : MockHttpClientResponseListener
    {
        return new DefaultMockHttpClientResponseListener();
    }
}