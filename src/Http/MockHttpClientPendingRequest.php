<?php

namespace MagpieLib\TestBench\Http;

use Exception;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Http\Bodies\HttpEncodedClientRequestBody;
use Magpie\Facades\Http\Bodies\HttpFormClientRequestBody;
use Magpie\Facades\Http\HttpClientPendingRequest;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\Facades\Http\HttpClientResponse;
use Magpie\General\Contexts\ScopedCollection;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Request;
use Magpie\Objects\Uri;
use Magpie\Routes\RouteRegistry;
use Magpie\Routes\RouteRun;
use Magpie\System\Kernel\Kernel;
use MagpieLib\TestBench\Http\Impls\MockRequest;
use MagpieLib\TestBench\Http\Impls\MockServerCollection;

/**
 * Pending HTTP client request for testing purpose (mocking)
 */
abstract class MockHttpClientPendingRequest extends HttpClientPendingRequest
{
    /**
     * @var string Request method
     */
    protected string $method;
    /**
     * @var string Hostname
     */
    protected string $hostname;
    /**
     * @var string Request URL (path)
     */
    protected string $path;


    /**
     * Constructor
     * @param string $method
     * @param string $hostname
     * @param string $path
     * @param array<string, mixed> $parentHeaders
     * @param array<HttpClientRequestOption> $parentOptions
     * @throws SafetyCommonException
     */
    protected function __construct(string $method, string $hostname, string $path, array $parentHeaders, array $parentOptions)
    {
        parent::__construct($parentHeaders, $parentOptions);

        $this->method = $method;
        $this->hostname = $hostname;
        $this->path = $path;
    }


    /**
     * @inheritDoc
     */
    public static final function getTypeClass() : string
    {
        return MockHttpClient::TYPECLASS;
    }


    /**
     * @inheritDoc
     * @throws Exception
     */
    protected final function onRequest() : HttpClientResponse
    {
        $scheme = $this->onGetScheme();
        $httpVersion = $this->onGetHttpVersion();
        $pathUri = Uri::parse($this->path);

        $variables = [];

        // Check document root and URI
        $documentRoot = $this->onGetDocumentRoot();
        $documentUri = $this->onGetDocumentUri();

        $fullDocumentUri = $documentRoot;
        if (str_ends_with($documentRoot, '/')) {
            $fullDocumentUri = substr($fullDocumentUri, 0, -1);
        }
        $fullDocumentUri .= $documentUri;

        // Prepare simulated variables
        $variables['DOCUMENT_ROOT'] = $documentRoot;
        $variables['DOCUMENT_URI'] = $documentUri;
        $variables['SCRIPT_FILENAME'] = $fullDocumentUri;
        $variables['SCRIPT_NAME'] = $documentUri;

        // Copy server variables from server
        $copyKeys = $this->onPreRequestCopyServerVariableKeys();
        $this->copyServerVariables($variables, $copyKeys);

        // Set request time
        $variables['REQUEST_TIME_FLOAT'] = gettimeofday(true);
        $variables['REQUEST_TIME'] = time();

        // Setup server variables
        $setupVariables = $this->onPreRequestSetupServerVariables($scheme, $httpVersion, $pathUri);
        $variables = array_merge($variables, iter_flatten($setupVariables));

        // Check headers
        $this->onPreRequestCheckHeaders();

        // Process request body (may update headers)
        $this->processRequestBody($posts, $postBody);

        // Create the corresponding server variable
        $serverVars = MockServerCollection::simulate($variables, $this->headers);

        // Create the mock request
        $builtUri = $pathUri->build();
        $request = MockRequest::simulate($builtUri->getQueries(), $posts, $postBody, $serverVars);

        return $this->onRunRequest($request, $scheme, $httpVersion);
    }


    /**
     * Process request body
     * @param array<string, mixed>|null $posts
     * @param string|null $postBody
     * @return void
     * @throws SafetyCommonException
     */
    private function processRequestBody(?array &$posts = null, ?string &$postBody = null) : void
    {
        $posts = [];
        $postBody = '';

        $this->onProcessRequestBody($posts, $postBody);
    }


    /**
     * Process request body
     * @param array<string, mixed> $posts
     * @param string $postBody
     * @return void
     * @throws SafetyCommonException
     */
    protected function onProcessRequestBody(array &$posts, string &$postBody) : void
    {
        if ($this->body === null) return;

        if ($this->body instanceof HttpFormClientRequestBody) {
            $posts = $this->body->checkKeyValues();
            return;
        }

        if ($this->body instanceof HttpEncodedClientRequestBody) {
            $postBody = $this->body->getEncodedBody();
            $this->withOverriddenHeader(CommonHttpHeader::CONTENT_TYPE, $this->body->getContentType());
            $this->withOverriddenHeader(CommonHttpHeader::CONTENT_LENGTH, '' . $this->body->getContentLength());
            return;
        }

        throw new UnsupportedValueException($this->body, _l('request body'));
    }


    /**
     * With header overridden, removing any existing customization
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected final function withOverriddenHeader(string $name, mixed $value) : static
    {
        return $this
            ->withoutHeader($name)
            ->withHeader($name, $value)
            ;
    }


    /**
     * Run the request
     * @param MockRequest $request
     * @param string $scheme
     * @param string $httpVersion
     * @return HttpClientResponse
     * @throws Exception
     */
    private function onRunRequest(MockRequest $request, string $scheme, string $httpVersion) : HttpClientResponse
    {
        $listener = $this->createRenderListener();
        $scoped = new ScopedCollection($listener->getScopedItems());

        try {
            $response = $this->onRouteRequest($this->hostname, $request);
            $ret = $listener->createResponse($scheme, $httpVersion, $request, $response);

            $scoped->succeeded();
            return $ret;
        } catch (Exception $ex) {
            $scoped->crash($ex);
            throw $ex;
        } finally {
            $scoped->release();
        }
    }


    /**
     * Route the request
     * @param string $hostname
     * @param Request $request
     * @return Renderable
     * @throws Exception
     */
    protected final function onRouteRequest(string $hostname, Request $request) : Renderable
    {
        $appConfig = Kernel::current()->getConfig();

        /** @noinspection PhpInternalEntityUsedInspection */
        $routeDomain = RouteRegistry::_route($hostname, $request);

        return RouteRun::create($appConfig)->run($routeDomain, $request);
    }


    /**
     * Create a render listener
     * @return MockHttpClientResponseListener
     */
    protected abstract function createRenderListener() : MockHttpClientResponseListener;


    /**
     * Get the keys to be copied from server variables
     * @return iterable<string>
     */
    protected function onPreRequestCopyServerVariableKeys() : iterable
    {
        yield 'PHP_SELF';

        yield 'SERVER_SOFTWARE';
        yield 'GATEWAY_INTERFACE';
    }


    /**
     * Setup server variables
     * @param string $scheme
     * @param string $httpVersion
     * @param Uri $pathUri
     * @return iterable<string, mixed>
     */
    protected function onPreRequestSetupServerVariables(string $scheme, string $httpVersion, Uri $pathUri) : iterable
    {
        yield 'REQUEST_SCHEME' => strtolower($scheme);
        yield 'SERVER_PROTOCOL' => strtoupper("$scheme/$httpVersion");
        yield 'SERVER_NAME' => $this->hostname;
        yield 'REQUEST_METHOD' => strtoupper($this->method);
        yield 'REQUEST_URI' => "$pathUri";
        yield 'QUERY_STRING' => $pathUri->query ?? '';

        yield 'SERVER_ADDR' => '127.0.0.1';
        yield 'SERVER_PORT' => '80';
        yield 'REMOTE_ADDR' => '127.0.0.1';
        yield 'REMOTE_PORT' => '65535';
    }


    /**
     * Check the headers before request
     * @return void
     */
    protected function onPreRequestCheckHeaders() : void
    {
        $this->withOverrideHeader('host', $this->hostname, false);
        $this->withOverrideHeader('user-agent', $this->onGetDefaultUserAgent());
        $this->withOverrideHeader('accept', $this->onGetDefaultAcceptHeader());
        $this->withOverrideHeader('cache-control', 'no-cache');
    }


    /**
     * Copy server variables
     * @param array<string, mixed> $variables
     * @param iterable<string> $keys
     * @return void
     */
    private function copyServerVariables(array &$variables, iterable $keys) : void
    {
        foreach ($keys as $key) {
            $key = strtoupper($key);
            if (!array_key_exists($key, $_SERVER)) continue;
            $variables[$key] = $_SERVER[$key];
        }
    }


    /**
     * The simulated scheme
     * @return string
     */
    protected function onGetScheme() : string
    {
        return 'http';
    }


    /**
     * The simulated HTTP version
     * @return string
     */
    protected function onGetHttpVersion() : string
    {
        return '1.0';
    }


    /**
     * The simulated document root
     * @return string
     */
    protected function onGetDocumentRoot() : string
    {
        $ret = project_path('');
        if (str_ends_with($ret, '/')) $ret = substr($ret, 0, -1);

        return $ret;
    }


    /**
     * The simulated document URI
     * @return string
     */
    protected function onGetDocumentUri() : string
    {
        return '/index.php';
    }


    /**
     * Default user agent
     * @return string
     */
    protected function onGetDefaultUserAgent() : string
    {
        return 'MagpieTestBench/1.0';
    }


    /**
     * Default accept
     * @return string
     */
    protected function onGetDefaultAcceptHeader() : string
    {
        return '*/*';
    }


    /**
     * With overriding header
     * @param string $name
     * @param mixed $value
     * @param bool $isDefault
     * @return void
     */
    private function withOverrideHeader(string $name, mixed $value, bool $isDefault = true) : void
    {
        if ($isDefault) {
            $key = static::normalizeHeader($name);
            if (array_key_exists($key, $this->headers)) return;
        }

        $this->withHeader($name, $value);
    }
}