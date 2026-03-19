<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Exception;

use Ecourty\PresidioClient\Exception\ApiException;
use Ecourty\PresidioClient\Exception\PresidioException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiException::class)]
#[CoversClass(PresidioException::class)]
class ApiExceptionTest extends TestCase
{
    public function testApiExceptionProperties(): void
    {
        $exception = new ApiException(422, '{"error":"unprocessable"}');

        self::assertSame(422, $exception->getStatusCode());
        self::assertSame('{"error":"unprocessable"}', $exception->getResponseBody());
        self::assertStringContainsString('HTTP 422', $exception->getMessage());
        self::assertInstanceOf(PresidioException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
    }
}
