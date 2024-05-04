<?php

namespace MagpieLib\TestBench\Http;

use Carbon\Carbon;
use Closure;
use Exception;
use Magpie\Codecs\Formats\HttpDateTimeFormatter;
use Magpie\Facades\Http\Bodies\HttpSimpleClientResponseBody;
use Magpie\Facades\Http\HttpClientResponse;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\HttpServer\OutputBufferCapture;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Request;
use Magpie\Routes\CommonRouteResponseListener;
use MagpieLib\TestBench\Exceptions\InvalidMockResponseException;
use MagpieLib\TestBench\Exceptions\TestException;
use Throwable;

/**
 * Listener to render response for mock HTTP client
 */
abstract class MockHttpClientResponseListener
{
    /**
     * @var OutputBufferCapture|null The output buffer capture in scope
     */
    private ?OutputBufferCapture $outputCapture = null;
    /**
     * @var int|null Cached HTTP status code returned
     */
    private ?int $httpStatusCode = null;
    /**
     * @var array<string, array<string>> Response headers received
     */
    private array $headers = [];


    /**
     * All scoped items
     * @return iterable<Scoped>
     * @throws Exception
     */
    public function getScopedItems() : iterable
    {
        $this->outputCapture = new OutputBufferCapture();
        yield $this->outputCapture;
        yield $this->createRenderListener()->createListeningScoped();
    }


    /**
     * Create response
     * @param string $scheme
     * @param string $httpVersion
     * @param Request $request
     * @param Renderable $response
     * @return HttpClientResponse
     * @throws TestException
     */
    public final function createResponse(string $scheme, string $httpVersion, Request $request, Renderable $response) : HttpClientResponse
    {
        $this->onSetupDefaultHeaders();

        $response->render($request);

        $bodyContent = $this->captureResponse();
        $body = HttpSimpleClientResponseBody::fromContent($bodyContent);

        $httpStatusCode = $this->httpStatusCode ?? CommonHttpStatusCode::OK;
        $headerLines = $this->flattenHeaderLines();

        return $this->createActualResponse($scheme, $httpVersion, $httpStatusCode, $headerLines, $body);
    }


    /**
     * Setup default headers
     * @return void
     */
    protected function onSetupDefaultHeaders() : void
    {
        $this->receiveHeaderLine('Server: ' . $this->getServerSoftwareName());
        $this->receiveHeaderLine('Date: ' . HttpDateTimeFormatter::create()->format(Carbon::now()));
        $this->receiveHeaderLine('Content-Type: ' . $this->getDefaultServerContentType());
        $this->receiveHeaderLine('X-Power-By: PHP/' . phpversion());
    }


    /**
     * The simulated server (software) name
     * @return string
     */
    protected function getServerSoftwareName() : string
    {
        return 'mock';
    }


    /**
     * The default server content type (when not explicitly specified)
     * @return string
     */
    protected function getDefaultServerContentType() : string
    {
        return 'text/html; charset=UTF-8';
    }


    /**
     * Receive a header line
     * @param string $headerLine
     * @param bool $isReplacePrevious
     * @return void
     */
    protected final function receiveHeaderLine(string $headerLine, bool $isReplacePrevious = true) : void
    {
        $headerComponents = explode(':', $headerLine);
        $headerKey = strtolower($headerComponents[0]);

        $headerLines = $this->headers[$headerKey] ?? [];
        if ($isReplacePrevious) $headerLines = [];
        $headerLines[] = $headerLine;

        $this->headers[$headerKey] = $headerLines;
    }


    /**
     * Capture the response
     * @return string
     * @throws TestException
     */
    protected final function captureResponse() : string
    {
        try {
            if ($this->outputCapture === null) throw new Exception('Missing OB capture');
            return $this->outputCapture->capture();
        } catch (TestException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new InvalidMockResponseException(previous: $ex);
        }
    }


    /**
     * Create the actual response
     * @param string $scheme
     * @param string $httpVersion
     * @param int $httpStatusCode
     * @param iterable<string> $headerLines
     * @param HttpClientResponseBody $body
     * @return MockHttpClientResponse
     */
    protected abstract function createActualResponse(string $scheme, string $httpVersion, int $httpStatusCode, iterable $headerLines, HttpClientResponseBody $body) : MockHttpClientResponse;





    /**
     * Flatten into the final header lines
     * @return iterable<string>
     */
    private function flattenHeaderLines() : iterable
    {
        foreach ($this->headers as $lines) {
            foreach ($lines as $line) {
                yield $line;
            }
        }
    }


    /**
     * Create a render listener
     * @return CommonRouteResponseListener
     */
    private function createRenderListener() : CommonRouteResponseListener
    {
        $onHttpResponseCode = function (int $code) {
            $this->httpStatusCode = $code;
        };

        return new class($onHttpResponseCode, $this->receiveHeaderLine(...)) extends CommonRouteResponseListener {
            /**
             * Constructor
             * @param Closure $onHttpResponseCode
             * @param Closure $onHeader
             */
            public function __construct(
                protected readonly Closure $onHttpResponseCode,
                protected readonly Closure $onHeader,
            ) {
                parent::__construct();
            }


            /**
             * @inheritDoc
             */
            protected function onSpecificHttpResponseCode(int $code) : void
            {
                parent::onSpecificHttpResponseCode($code);

                ($this->onHttpResponseCode)($code);
            }


            /**
             * @inheritDoc
             */
            protected function onSpecificHeader(string $headerLine, bool $isReplacePrevious) : void
            {
                parent::onSpecificHeader($headerLine, $isReplacePrevious);

                ($this->onHeader)($headerLine, $isReplacePrevious);
            }
        };
    }
}