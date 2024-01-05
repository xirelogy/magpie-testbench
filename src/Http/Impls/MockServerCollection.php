<?php

namespace MagpieLib\TestBench\Http\Impls;

use Magpie\General\Str;
use Magpie\HttpServer\ServerCollection;

/**
 * Mock ServerCollection
 * @internal
 */
class MockServerCollection extends ServerCollection
{
    /**
     * Simulate a server collection
     * @param iterable<string, string> $variables
     * @param iterable<string, mixed> $headers
     * @return static
     */
    public static function simulate(iterable $variables, iterable $headers) : static
    {
        $keyValues = [];

        foreach ($variables as $key => $value) {
            $outKey = strtoupper($key);
            if (str_starts_with($outKey, 'HTTP_')) continue;    // Prevents unintended pollution
            $keyValues[$outKey] = $value;
        }

        foreach (static::translateHeaders($headers) as $headerKey => $headerValue) {
            $outHeaderKey = str_replace('-', '_', strtoupper($headerKey));
            $keyValues['HTTP_' . $outHeaderKey] = $headerValue;
        }

        ksort($keyValues);

        return new static($keyValues);
    }


    /**
     * Translate headers
     * @param iterable<string, mixed> $headers
     * @return iterable<string, string>
     */
    protected static function translateHeaders(iterable $headers) : iterable
    {
        foreach ($headers as $headerName => $headerValue) {
            if (is_array($headerValue)) {
                $finalValues = [];
                foreach ($headerValue as $value) {
                    if (Str::isNullOrEmpty($value)) continue;
                    $finalValues[] = $value;
                }
                if (count($finalValues) > 0) {
                    yield $headerName => implode('; ', $finalValues);
                } else {
                    yield $headerName => '';
                }
            } else {
                yield $headerName => $headerValue;
            }
        }
    }
}