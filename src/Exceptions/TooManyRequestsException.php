<?php


namespace Bitpin\Api\Exceptions;

class TooManyRequestsException extends BitpinApiException
{
    public function __construct(
        string      $message = 'Bitpin API rate limit exceeded.',
        int         $code = 429,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}