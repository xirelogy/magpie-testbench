<?php

namespace MagpieLib\TestBench\Exceptions;

use Throwable;

/**
 * The response from mock request is invalid
 */
class InvalidMockResponseException extends TestException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Invalid mock response');

        parent::__construct($message, $previous, $code);
    }
}