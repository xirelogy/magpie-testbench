<?php

namespace MagpieLib\TestBench\Http;

use Magpie\Facades\Http\HttpClientRequestTimeStatistics;
use Magpie\Facades\Http\HttpClientResponse;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\Facades\Http\HttpClientResponseHeaders;
use MagpieLib\TestBench\Http\Impls\MockHttpClientResponseHeaders;

/**
 * HTTP client response from mocked HTTP client
 */
abstract class MockHttpClientResponse extends HttpClientResponse
{
    /**
     * @var string Specific HTTP scheme
     */
    protected readonly string $scheme;
    /**
     * @var string Specific HTTP version
     */
    protected readonly string $httpVersion;
    /**
     * @var int HTTP status code
     */
    protected readonly int $httpStatusCode;
    /**
     * @var HttpClientResponseHeaders Response headers
     */
    protected readonly HttpClientResponseHeaders $headers;
    /**
     * @var HttpClientResponseBody Content body
     */
    protected readonly HttpClientResponseBody $body;


    /**
     * Constructor
     * @param string $scheme
     * @param string $httpVersion
     * @param int $httpStatusCode
     * @param iterable $headerLines
     * @param HttpClientResponseBody $body
     */
    protected function __construct(string $scheme, string $httpVersion, int $httpStatusCode, iterable $headerLines, HttpClientResponseBody $body)
    {
        $this->scheme = $scheme;
        $this->httpVersion = $httpVersion;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = MockHttpClientResponseHeaders::parse($headerLines);
        $this->body = $body;
    }


    /**
     * @inheritDoc
     */
    public final function getScheme() : string
    {
        return $this->scheme;
    }


    /**
     * @inheritDoc
     */
    public final function getHttpVersion() : ?string
    {
        return $this->httpVersion;
    }


    /**
     * @inheritDoc
     */
    public final function getHttpStatusCode() : int
    {
        return $this->httpStatusCode;
    }


    /**
     * @inheritDoc
     */
    public final function getHeaders() : HttpClientResponseHeaders
    {
        return $this->headers;
    }


    /**
     * @inheritDoc
     */
    public final function getBody() : HttpClientResponseBody
    {
        return $this->body;
    }


    /**
     * @inheritDoc
     */
    public function getCertificates() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getLocalAddress() : ?string
    {
        return '127.0.0.1';
    }


    /**
     * @inheritDoc
     */
    public function getRemoteAddress() : ?string
    {
        return '127.0.0.1';
    }


    /**
     * @inheritDoc
     */
    public final function getTimeStatistics() : HttpClientRequestTimeStatistics
    {
        $ret = new HttpClientRequestTimeStatistics();
        $this->onGetTimeStatistics($ret);
        return $ret;
    }


    /**
     * Update time statistics
     * @param HttpClientRequestTimeStatistics $stats
     * @return void
     */
    protected function onGetTimeStatistics(HttpClientRequestTimeStatistics $stats) : void
    {
        // Default NOP
    }
}