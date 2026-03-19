<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Exception;

class ApiException extends PresidioException
{
    public function __construct(
        private readonly int $statusCode,
        private readonly string $responseBody,
    ) {
        parent::__construct(\sprintf('Presidio API error (HTTP %d): %s', $statusCode, $responseBody));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
