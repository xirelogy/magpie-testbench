<?php

namespace MagpieLib\TestBench\Http;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpClient;
use Magpie\Logs\Concepts\Loggable;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * HTTP client for testing purpose (mocking)
 */
abstract class MockHttpClient extends HttpClient
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'mock';
    /**
     * @var Loggable|null Logger instance to use
     */
    protected ?Loggable $useLogger = null;
    /**
     * @var string|null The preferred hostname to be used, when not specified
     */
    protected ?string $useHostname = null;


    /**
     * @inheritDoc
     */
    public static final function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function setLogger(Loggable $logger) : bool
    {
        $this->useLogger = $logger;
        return true;
    }


    /**
     * Specify the hostname to be used
     * @param string $hostname
     * @return $this
     */
    public final function useHostname(string $hostname) : static
    {
        $this->useHostname = $hostname;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function prepare(string $method, string $url) : MockHttpClientPendingRequest
    {
        if (str_starts_with($url, '/')) {
            $hostname = $this->useHostname ?? 'localhost';
            $path = $url;
        } else {
            $slashPos = strpos($url, '/');
            if ($slashPos !== false) {
                $hostname = substr($url, 0, $slashPos);
                $path = substr($url, $slashPos);
            } else {
                $hostname = $url;
                $path = '/';
            }
        }

        return $this->createPendingRequest($method, $hostname, $path);
    }


    /**
     * Create the corresponding pending request as prepared
     * @param string $method
     * @param string $hostname
     * @param string $path
     * @return MockHttpClientPendingRequest
     * @throws SafetyCommonException
     */
    protected abstract function createPendingRequest(string $method, string $hostname, string $path) : MockHttpClientPendingRequest;


    /**
     * @inheritDoc
     */
    protected static final function specificInitialize() : static
    {
        return new static();
    }


    /**
     * @inheritDoc
     */
    public static final function systemBootRegister(BootRegistrar $registrar) : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public static final function systemBoot(BootContext $context) : void
    {
        // MockHttpClient(s) are not proper clients and should do nothing during boot
    }
}