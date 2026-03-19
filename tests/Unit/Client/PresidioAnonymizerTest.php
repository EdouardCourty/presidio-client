<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Client;

use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Exception\ApiException;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\AnonymizedEntity;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\AnonymizeResult;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(PresidioAnonymizer::class)]
#[CoversClass(AnonymizeRequest::class)]
#[CoversClass(AnonymizeResult::class)]
#[CoversClass(AnonymizedEntity::class)]
#[CoversClass(DeanonymizeRequest::class)]
#[CoversClass(DeanonymizeResult::class)]
#[CoversClass(AnalyzerResult::class)]
#[CoversClass(ApiException::class)]
class PresidioAnonymizerTest extends TestCase
{
    public function testAnonymize(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'text' => 'My email is <EMAIL_ADDRESS>, phone: <PHONE_NUMBER>',
            'items' => [
                [
                    'operator' => 'replace',
                    'entity_type' => 'EMAIL_ADDRESS',
                    'start' => 12,
                    'end' => 27,
                    'text' => '<EMAIL_ADDRESS>',
                ],
                [
                    'operator' => 'replace',
                    'entity_type' => 'PHONE_NUMBER',
                    'start' => 35,
                    'end' => 49,
                    'text' => '<PHONE_NUMBER>',
                ],
            ],
        ], \JSON_THROW_ON_ERROR));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        $request = new AnonymizeRequest(
            text: 'My email is john@example.com, phone: 555-123-4567',
            analyzerResults: [
                new AnalyzerResult('EMAIL_ADDRESS', 12, 28, 1.0),
                new AnalyzerResult('PHONE_NUMBER', 36, 48, 0.75),
            ],
        );

        $result = $anonymizer->anonymize($request);

        self::assertStringContainsString('<EMAIL_ADDRESS>', $result->getText());
        self::assertCount(2, $result->getItems());
        self::assertSame('replace', $result->getItems()[0]->getOperator());
        self::assertSame('EMAIL_ADDRESS', $result->getItems()[0]->getEntityType());
    }

    public function testAnonymizeThrowsOnApiError(): void
    {
        $mockResponse = new MockResponse('{"error":"server error"}', [
            'http_code' => 500,
        ]);

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        $request = new AnonymizeRequest(
            text: 'test',
            analyzerResults: [],
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/HTTP 500/');

        $anonymizer->anonymize($request);
    }

    public function testDeanonymize(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'text' => 'My email is john@example.com',
        ], \JSON_THROW_ON_ERROR));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        $request = new DeanonymizeRequest(
            text: 'My email is <EMAIL_ADDRESS>',
            anonymizerResults: [
                new AnonymizedEntity('replace', 'EMAIL_ADDRESS', 12, 27, '<EMAIL_ADDRESS>'),
            ],
        );

        $result = $anonymizer->deanonymize($request);

        self::assertSame('My email is john@example.com', $result->getText());
    }

    public function testGetAnonymizers(): void
    {
        $mockResponse = new MockResponse(json_encode(
            ['replace', 'redact', 'hash', 'mask', 'encrypt'],
            \JSON_THROW_ON_ERROR,
        ));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        $anonymizers = $anonymizer->getAnonymizers();

        self::assertSame(['replace', 'redact', 'hash', 'mask', 'encrypt'], $anonymizers);
    }

    public function testGetDeanonymizers(): void
    {
        $mockResponse = new MockResponse(json_encode(
            ['decrypt'],
            \JSON_THROW_ON_ERROR,
        ));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        $deanonymizers = $anonymizer->getDeanonymizers();

        self::assertSame(['decrypt'], $deanonymizers);
    }

    public function testHealthReturnsTrue(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);

        $client = new MockHttpClient($mockResponse, 'http://localhost:5002');
        $anonymizer = new PresidioAnonymizer(httpClient: $client);

        self::assertTrue($anonymizer->health());
    }
}
