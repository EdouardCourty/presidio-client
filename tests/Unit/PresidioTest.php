<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit;

use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\AnonymizedEntity;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\AnonymizeResult;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeResult;
use Ecourty\PresidioClient\Model\OperatorConfig;
use Ecourty\PresidioClient\Model\PresidioHealth;
use Ecourty\PresidioClient\Presidio;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Presidio::class)]
#[CoversClass(PresidioHealth::class)]
#[CoversClass(PresidioAnalyzer::class)]
#[CoversClass(PresidioAnonymizer::class)]
#[CoversClass(AnalyzerRequest::class)]
#[CoversClass(AnalyzerResult::class)]
#[CoversClass(AnonymizeRequest::class)]
#[CoversClass(AnonymizeResult::class)]
#[CoversClass(AnonymizedEntity::class)]
#[CoversClass(DeanonymizeRequest::class)]
#[CoversClass(DeanonymizeResult::class)]
class PresidioTest extends TestCase
{
    private function createPresidio(MockResponse|MockHttpClient $analyzerResponse, MockResponse|MockHttpClient $anonymizerResponse): Presidio
    {
        $analyzerClient = $analyzerResponse instanceof MockHttpClient
            ? $analyzerResponse
            : new MockHttpClient($analyzerResponse, 'http://localhost:5001');

        $anonymizerClient = $anonymizerResponse instanceof MockHttpClient
            ? $anonymizerResponse
            : new MockHttpClient($anonymizerResponse, 'http://localhost:5002');

        return new Presidio(
            analyzer: new PresidioAnalyzer(httpClient: $analyzerClient),
            anonymizer: new PresidioAnonymizer(httpClient: $anonymizerClient),
        );
    }

    public function testAnonymizeConvenience(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse(json_encode([
                ['entity_type' => 'PERSON', 'start' => 11, 'end' => 21, 'score' => 0.85],
            ], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'text' => 'My name is <PERSON>',
                'items' => [
                    ['operator' => 'replace', 'entity_type' => 'PERSON', 'start' => 11, 'end' => 19, 'text' => '<PERSON>'],
                ],
            ], \JSON_THROW_ON_ERROR)),
        );

        $result = $presidio->anonymize('My name is John Smith');

        self::assertSame('My name is <PERSON>', $result->getText());
        self::assertCount(1, $result->getItems());
        self::assertSame('PERSON', $result->getItems()[0]->getEntityType());
    }

    public function testAnonymizeConvenienceWithOperators(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse(json_encode([
                ['entity_type' => 'PERSON', 'start' => 11, 'end' => 21, 'score' => 0.85],
            ], \JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'text' => 'My name is [REDACTED]',
                'items' => [
                    ['operator' => 'replace', 'entity_type' => 'PERSON', 'start' => 11, 'end' => 21, 'text' => '[REDACTED]'],
                ],
            ], \JSON_THROW_ON_ERROR)),
        );

        $result = $presidio->anonymize(
            text: 'My name is John Smith',
            operators: [
                'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => '[REDACTED]']),
            ],
        );

        self::assertSame('My name is [REDACTED]', $result->getText());
    }

    public function testHealthBothUp(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse('', ['http_code' => 200]),
            new MockResponse('', ['http_code' => 200]),
        );

        $health = $presidio->health();

        self::assertInstanceOf(PresidioHealth::class, $health);
        self::assertTrue($health->isAnalyzerHealthy());
        self::assertTrue($health->isAnonymizerHealthy());
        self::assertTrue($health->isHealthy());
    }

    public function testHealthAnalyzerDown(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse('', ['http_code' => 503]),
            new MockResponse('', ['http_code' => 200]),
        );

        $health = $presidio->health();

        self::assertFalse($health->isAnalyzerHealthy());
        self::assertTrue($health->isAnonymizerHealthy());
        self::assertFalse($health->isHealthy());
    }

    public function testAnalyzeProxy(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse(json_encode([
                ['entity_type' => 'EMAIL_ADDRESS', 'start' => 12, 'end' => 28, 'score' => 1.0],
            ], \JSON_THROW_ON_ERROR)),
            new MockHttpClient([], 'http://localhost:5002'),
        );

        $results = $presidio->analyze(new AnalyzerRequest(text: 'My email is john@example.com'));

        self::assertCount(1, $results);
        self::assertSame('EMAIL_ADDRESS', $results[0]->getEntityType());
    }

    public function testAnonymizeRequestProxy(): void
    {
        $presidio = $this->createPresidio(
            new MockHttpClient([], 'http://localhost:5001'),
            new MockResponse(json_encode([
                'text' => 'My name is <PERSON>',
                'items' => [
                    ['operator' => 'replace', 'entity_type' => 'PERSON', 'start' => 11, 'end' => 19, 'text' => '<PERSON>'],
                ],
            ], \JSON_THROW_ON_ERROR)),
        );

        $result = $presidio->anonymizeRequest(new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [new AnalyzerResult('PERSON', 11, 21, 0.85)],
        ));

        self::assertSame('My name is <PERSON>', $result->getText());
    }

    public function testDeanonymizeProxy(): void
    {
        $presidio = $this->createPresidio(
            new MockHttpClient([], 'http://localhost:5001'),
            new MockResponse(json_encode([
                'text' => 'My name is John Smith',
            ], \JSON_THROW_ON_ERROR)),
        );

        $result = $presidio->deanonymize(new DeanonymizeRequest(
            text: 'My name is <PERSON>',
            anonymizerResults: [new AnonymizedEntity('replace', 'PERSON', 11, 19, '<PERSON>')],
        ));

        self::assertSame('My name is John Smith', $result->getText());
    }

    public function testGetSupportedEntitiesProxy(): void
    {
        $presidio = $this->createPresidio(
            new MockResponse(json_encode(
                ['EMAIL_ADDRESS', 'PHONE_NUMBER'],
                \JSON_THROW_ON_ERROR,
            )),
            new MockHttpClient([], 'http://localhost:5002'),
        );

        self::assertSame(['EMAIL_ADDRESS', 'PHONE_NUMBER'], $presidio->getSupportedEntities());
    }

    public function testGetAnonymizersProxy(): void
    {
        $presidio = $this->createPresidio(
            new MockHttpClient([], 'http://localhost:5001'),
            new MockResponse(json_encode(
                ['replace', 'redact', 'hash'],
                \JSON_THROW_ON_ERROR,
            )),
        );

        self::assertSame(['replace', 'redact', 'hash'], $presidio->getAnonymizers());
    }

    public function testGetClientAccessors(): void
    {
        $presidio = new Presidio();

        self::assertInstanceOf(PresidioAnalyzer::class, $presidio->getAnalyzer());
        self::assertInstanceOf(PresidioAnonymizer::class, $presidio->getAnonymizer());
    }
}
