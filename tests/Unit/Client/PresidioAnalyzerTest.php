<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Client;

use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Exception\ApiException;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\RecognizerResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(PresidioAnalyzer::class)]
#[CoversClass(AnalyzerRequest::class)]
#[CoversClass(AnalyzerResult::class)]
#[CoversClass(RecognizerResult::class)]
#[CoversClass(ApiException::class)]
class PresidioAnalyzerTest extends TestCase
{
    public function testAnalyzeReturnsResults(): void
    {
        $mockResponse = new MockResponse(json_encode([
            ['entity_type' => 'EMAIL_ADDRESS', 'start' => 16, 'end' => 33, 'score' => 1.0],
            ['entity_type' => 'PHONE_NUMBER', 'start' => 41, 'end' => 53, 'score' => 0.75],
        ], \JSON_THROW_ON_ERROR));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        $request = new AnalyzerRequest(
            text: 'My email is john@example.com, phone: 555-123-4567',
        );

        $results = $analyzer->analyze($request);

        self::assertCount(2, $results);
        self::assertSame('EMAIL_ADDRESS', $results[0]->getEntityType());
        self::assertSame(16, $results[0]->getStart());
        self::assertSame(33, $results[0]->getEnd());
        self::assertSame(1.0, $results[0]->getScore());
        self::assertSame('PHONE_NUMBER', $results[1]->getEntityType());
    }

    public function testAnalyzeThrowsOnApiError(): void
    {
        $mockResponse = new MockResponse('{"error":"bad request"}', [
            'http_code' => 400,
        ]);

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        $request = new AnalyzerRequest(text: 'test');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/HTTP 400/');

        $analyzer->analyze($request);
    }

    public function testGetRecognizers(): void
    {
        $mockResponse = new MockResponse(json_encode([
            [
                'name' => 'EmailRecognizer',
                'supported_entities' => ['EMAIL_ADDRESS'],
                'supported_language' => ['en'],
            ],
            [
                'name' => 'PhoneRecognizer',
                'supported_entities' => ['PHONE_NUMBER'],
                'supported_language' => ['en', 'fr'],
            ],
        ], \JSON_THROW_ON_ERROR));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        $recognizers = $analyzer->getRecognizers();

        self::assertCount(2, $recognizers);
        self::assertInstanceOf(RecognizerResult::class, $recognizers[0]);
        self::assertSame('EmailRecognizer', $recognizers[0]->getName());
        self::assertSame(['EMAIL_ADDRESS'], $recognizers[0]->getSupportedEntities());
        self::assertSame(['en'], $recognizers[0]->getSupportedLanguages());
        self::assertSame('PhoneRecognizer', $recognizers[1]->getName());
    }

    public function testGetSupportedEntities(): void
    {
        $mockResponse = new MockResponse(json_encode(
            ['EMAIL_ADDRESS', 'PHONE_NUMBER', 'PERSON'],
            \JSON_THROW_ON_ERROR,
        ));

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        $entities = $analyzer->getSupportedEntities();

        self::assertSame(['EMAIL_ADDRESS', 'PHONE_NUMBER', 'PERSON'], $entities);
    }

    public function testHealthReturnsTrue(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        self::assertTrue($analyzer->health());
    }

    public function testHealthReturnsFalse(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 503]);

        $client = new MockHttpClient($mockResponse, 'http://localhost:5001');
        $analyzer = new PresidioAnalyzer(httpClient: $client);

        self::assertFalse($analyzer->health());
    }
}
