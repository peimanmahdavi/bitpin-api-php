<?php

namespace Bitpin\Api\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class BitpinApiException extends Exception
{
    protected int $httpStatusCode;
    protected ?array $errorDetails;
    protected ?ResponseInterface $response;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        int $httpStatusCode = 0,
        ?array $errorDetails = null,
        ?ResponseInterface $response = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
        $this->errorDetails = $errorDetails;
        $this->response = $response;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public static function fromResponse(ResponseInterface $response, string $body): self
    {
        $statusCode = $response->getStatusCode();
        $errorDetails = null;
        $message = "Bitpin API request failed with status {$statusCode}";

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $errorDetails = $decoded;
            
            // Extract meaningful message from various error formats
            if (isset($decoded['detail'])) {
                $message = $decoded['detail'];
            } elseif (isset($decoded['message'])) {
                $message = $decoded['message'];
            } elseif (isset($decoded['error'])) {
                $message = $decoded['error'];
            } elseif (isset($decoded['code']) && isset($decoded['messages'])) {
                $message = $decoded['messages'][0]['message'] ?? $decoded['code'];
            }
        }

        return new self($message, $statusCode, null, $statusCode, $errorDetails, $response);
    }

    public static function authenticationRequired(): self
    {
        return new self(
            'Authentication required. Please provide valid API credentials.',
            401,
            null,
            401,
            ['code' => 'authentication_required']
        );
    }

    public static function tokenExpired(): self
    {
        return new self(
            'Access token expired. Please refresh your token.',
            401,
            null,
            401,
            ['code' => 'token_expired']
        );
    }

    public static function rateLimited(int $retryAfter = 0): self
    {
        return new self(
            'Rate limit exceeded. Please slow down your requests.',
            429,
            null,
            429,
            ['code' => 'rate_limited', 'retry_after' => $retryAfter]
        );
    }

    public static function invalidCredentials(): self
    {
        return new self(
            'Invalid API key or secret key.',
            401,
            null,
            401,
            ['code' => 'invalid_credentials']
        );
    }

    public static function ipNotAllowed(): self
    {
        return new self(
            'Request from an IP not in the allowed list for this API key.',
            403,
            null,
            403,
            ['code' => 'ip_not_allowed']
        );
    }

    public static function notFound(string $resource = 'Resource'): self
    {
        return new self(
            "{$resource} not found.",
            404,
            null,
            404,
            ['code' => 'not_found']
        );
    }

    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self(
            $message,
            500,
            null,
            500,
            ['code' => 'server_error']
        );
    }

    public static function serviceUnavailable(): self
    {
        return new self(
            'Service temporarily unavailable. Please retry later.',
            503,
            null,
            503,
            ['code' => 'service_unavailable']
        );
    }

    public static function badRequest(string $message = 'Bad request'): self
    {
        return new self(
            $message,
            400,
            null,
            400,
            ['code' => 'bad_request']
        );
    }
}