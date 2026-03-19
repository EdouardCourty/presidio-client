<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Integration\Client;

use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\OperatorConfig;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests requiring running Presidio services.
 * Start them with: docker compose up -d
 *
 * Run with: composer test-integration
 */
class PresidioAnonymizerIntegrationTest extends TestCase
{
    private PresidioAnonymizer $anonymizer;

    protected function setUp(): void
    {
        $this->anonymizer = new PresidioAnonymizer('http://localhost:5002');

        if (!$this->anonymizer->health()) {
            self::markTestSkipped('Presidio Anonymizer is not running on http://localhost:5002');
        }
    }

    public function testHealth(): void
    {
        self::assertTrue($this->anonymizer->health());
    }

    public function testAnonymizeWithDefaultReplace(): void
    {
        $request = new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertSame('My name is <PERSON>', $result->getText());
        self::assertCount(1, $result->getItems());
        self::assertSame('replace', $result->getItems()[0]->getOperator());
        self::assertSame('PERSON', $result->getItems()[0]->getEntityType());
    }

    public function testAnonymizeWithCustomReplace(): void
    {
        $request = new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
            ],
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => 'REDACTED']),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertSame('My name is REDACTED', $result->getText());
    }

    public function testAnonymizeWithMask(): void
    {
        $request = new AnonymizeRequest(
            text: 'My phone is 555-123-4567',
            analyzerResults: [
                new AnalyzerResult('PHONE_NUMBER', 12, 24, 0.75),
            ],
            anonymizers: [
                'PHONE_NUMBER' => new OperatorConfig(OperatorType::MASK, [
                    'masking_char' => '*',
                    'chars_to_mask' => 12,
                    'from_end' => false,
                ]),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertStringContainsString('************', $result->getText());
        self::assertSame('mask', $result->getItems()[0]->getOperator());
    }

    public function testAnonymizeWithHash(): void
    {
        $request = new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
            ],
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::HASH, ['hash_type' => 'sha256']),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertStringNotContainsString('John Smith', $result->getText());
        self::assertSame('hash', $result->getItems()[0]->getOperator());
    }

    public function testAnonymizeWithRedact(): void
    {
        $request = new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
            ],
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::REDACT),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertSame('My name is ', $result->getText());
    }

    public function testAnonymizeWithEncryptAndDeanonymize(): void
    {
        $key = 'aaaaaaaaaaaaaaaa';

        $anonymizeRequest = new AnonymizeRequest(
            text: 'My name is John Smith',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
            ],
            anonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::ENCRYPT, ['key' => $key]),
            ],
        );

        $anonymizeResult = $this->anonymizer->anonymize($anonymizeRequest);

        self::assertStringNotContainsString('John Smith', $anonymizeResult->getText());
        self::assertSame('encrypt', $anonymizeResult->getItems()[0]->getOperator());

        $deanonymizeRequest = new DeanonymizeRequest(
            text: $anonymizeResult->getText(),
            anonymizerResults: $anonymizeResult->getItems(),
            deanonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::DECRYPT, ['key' => $key]),
            ],
        );

        $deanonymizeResult = $this->anonymizer->deanonymize($deanonymizeRequest);

        self::assertSame('My name is John Smith', $deanonymizeResult->getText());
    }

    public function testAnonymizeMultipleEntitiesWithDifferentOperators(): void
    {
        $request = new AnonymizeRequest(
            text: 'My name is John Smith, email: john@example.com',
            analyzerResults: [
                new AnalyzerResult('PERSON', 11, 21, 0.85),
                new AnalyzerResult('EMAIL_ADDRESS', 29, 46, 1.0),
            ],
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => '[NAME]']),
                'EMAIL_ADDRESS' => new OperatorConfig(OperatorType::REDACT),
            ],
        );

        $result = $this->anonymizer->anonymize($request);

        self::assertStringContainsString('[NAME]', $result->getText());
        self::assertStringNotContainsString('john@example.com', $result->getText());
        self::assertCount(2, $result->getItems());
    }

    public function testGetAnonymizers(): void
    {
        $anonymizers = $this->anonymizer->getAnonymizers();

        self::assertNotEmpty($anonymizers);
        self::assertContains('replace', $anonymizers);
        self::assertContains('hash', $anonymizers);
        self::assertContains('mask', $anonymizers);
        self::assertContains('encrypt', $anonymizers);
        self::assertContains('redact', $anonymizers);
    }

    public function testGetDeanonymizers(): void
    {
        $deanonymizers = $this->anonymizer->getDeanonymizers();

        self::assertNotEmpty($deanonymizers);
        self::assertContains('decrypt', $deanonymizers);
    }
}
