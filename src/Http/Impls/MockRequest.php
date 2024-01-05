<?php

namespace MagpieLib\TestBench\Http\Impls;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Str;
use Magpie\HttpServer\Concepts\UserCollectable;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\ServerCollection;

/**
 * A mock Magpie\HttpServer\Request
 * @internal
 */
class MockRequest extends Request
{
    /**
     * @var string Mocked body
     */
    protected readonly string $mockBody;


    /**
     * Constructor
     * @param UserCollectable $queries
     * @param UserCollectable $posts
     * @param UserCollectable $cookies
     * @param ServerCollection $serverVars
     * @param string $mockBody
     */
    protected function __construct(UserCollectable $queries, UserCollectable $posts, UserCollectable $cookies, ServerCollection $serverVars, string $mockBody)
    {
        parent::__construct($queries, $posts, $cookies, $serverVars);

        $this->mockBody = $mockBody;
    }


    /**
     * @inheritDoc
     */
    public function getBody() : string
    {
        return $this->mockBody;
    }


    /**
     * Simulate a request
     * @param iterable<string, string> $queries
     * @param iterable<string, mixed> $posts
     * @param string $postBody
     * @param ServerCollection $serverVars
     * @return static
     */
    public static function simulate(iterable $queries, iterable $posts, string $postBody, ServerCollection $serverVars) : static
    {
        $queries = static::createUserCollectionFrom($queries);
        $posts = static::createUserCollectionFrom($posts);

        $cookiesVars = static::parseCookiesFromServerVariables($serverVars);
        $cookies = static::createUserCollectionFrom(iter_flatten($cookiesVars));

        return new static($queries, $posts, $cookies, $serverVars, $postBody);
    }


    /**
     * Parse for cookies from server variables
     * @param ServerCollection $serverVars
     * @return iterable<string, string>
     */
    private static function parseCookiesFromServerVariables(ServerCollection $serverVars) : iterable
    {
        $headers = $serverVars->getHeaders();
        $cookieString = $headers->safeOptional(CommonHttpHeader::COOKIE, StringParser::create());
        if (Str::isNullOrEmpty($cookieString)) return;

        foreach (explode(';', $cookieString) as $cookieSpec) {
            $equalPos = strpos($cookieSpec, '=');
            if ($equalPos === false) continue;

            $key = substr($cookieSpec, 0, $equalPos);
            $value = substr($cookieSpec, $equalPos + 1);

            yield urldecode(trim($key)) => urldecode(trim($value));
        }
    }
}
