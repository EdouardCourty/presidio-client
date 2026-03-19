<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Integration;

use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\OperatorConfig;
use PHPUnit\Framework\TestCase;

/**
 * Full round-trip tests: analyze → anonymize → process → deanonymize.
 * Requires running Presidio services: docker compose up -d
 *
 * Run with: composer test-integration
 */
class RoundTripIntegrationTest extends TestCase
{
    private PresidioAnalyzer $analyzer;
    private PresidioAnonymizer $anonymizer;

    protected function setUp(): void
    {
        $this->analyzer = new PresidioAnalyzer('http://localhost:5001');
        $this->anonymizer = new PresidioAnonymizer('http://localhost:5002');

        if (!$this->analyzer->health() || !$this->anonymizer->health()) {
            self::markTestSkipped('Presidio services are not running (docker compose up -d)');
        }
    }

    public function testFullRoundTripWithEncrypt(): void
    {
        $originalText = 'My name is John Smith, my email is john@example.com and I live in New York';
        $key = 'aaaaaaaaaaaaaaaa';

        // 1. Analyze — detect PII
        $analyzerResults = $this->analyzer->analyze(new AnalyzerRequest(text: $originalText));
        self::assertNotEmpty($analyzerResults, 'Analyzer should detect PII entities');

        // 2. Anonymize — encrypt all PII
        $anonymizeResult = $this->anonymizer->anonymize(new AnonymizeRequest(
            text: $originalText,
            analyzerResults: $analyzerResults,
            anonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::ENCRYPT, ['key' => $key]),
            ],
        ));

        $anonymizedText = $anonymizeResult->getText();
        self::assertStringNotContainsString('John Smith', $anonymizedText);
        self::assertStringNotContainsString('john@example.com', $anonymizedText);

        // 3. Process — simulate doing something with the anonymized text (e.g. send to LLM)
        $processedText = $anonymizedText;

        // 4. Deanonymize — restore original PII
        $deanonymizeResult = $this->anonymizer->deanonymize(new DeanonymizeRequest(
            text: $processedText,
            anonymizerResults: $anonymizeResult->getItems(),
            deanonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::DECRYPT, ['key' => $key]),
            ],
        ));

        self::assertSame($originalText, $deanonymizeResult->getText());
    }

    public function testRoundTripPreservesNonPiiText(): void
    {
        $originalText = 'Please contact John at john@test.org for the project review.';
        $key = 'bbbbbbbbbbbbbbbb';

        $analyzerResults = $this->analyzer->analyze(new AnalyzerRequest(text: $originalText));

        $anonymizeResult = $this->anonymizer->anonymize(new AnonymizeRequest(
            text: $originalText,
            analyzerResults: $analyzerResults,
            anonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::ENCRYPT, ['key' => $key]),
            ],
        ));

        // Non-PII parts should still be readable
        self::assertStringContainsString('Please contact', $anonymizeResult->getText());
        self::assertStringContainsString('for the project review.', $anonymizeResult->getText());

        // Round-trip back
        $deanonymizeResult = $this->anonymizer->deanonymize(new DeanonymizeRequest(
            text: $anonymizeResult->getText(),
            anonymizerResults: $anonymizeResult->getItems(),
            deanonymizers: [
                'DEFAULT' => new OperatorConfig(OperatorType::DECRYPT, ['key' => $key]),
            ],
        ));

        self::assertSame($originalText, $deanonymizeResult->getText());
    }

    public function testAnalyzeThenAnonymizeWithReplace(): void
    {
        $text = 'My SSN is 078-05-1120 and my credit card is 4532015112830366';

        $results = $this->analyzer->analyze(new AnalyzerRequest(text: $text));

        $anonymized = $this->anonymizer->anonymize(new AnonymizeRequest(
            text: $text,
            analyzerResults: $results,
        ));

        self::assertStringNotContainsString('078-05-1120', $anonymized->getText());
        self::assertStringNotContainsString('4532015112830366', $anonymized->getText());
        self::assertNotEmpty($anonymized->getItems());
    }

    public function testAnalyzeThenAnonymizeWithMixedOperators(): void
    {
        $text = 'My name is Jane Doe and my email is jane@example.com';

        $results = $this->analyzer->analyze(new AnalyzerRequest(text: $text));

        $anonymized = $this->anonymizer->anonymize(new AnonymizeRequest(
            text: $text,
            analyzerResults: $results,
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => '[REDACTED_NAME]']),
                'EMAIL_ADDRESS' => new OperatorConfig(OperatorType::HASH, ['hash_type' => 'sha256']),
            ],
        ));

        self::assertStringContainsString('[REDACTED_NAME]', $anonymized->getText());
        self::assertStringNotContainsString('jane@example.com', $anonymized->getText());
        self::assertStringNotContainsString('Jane Doe', $anonymized->getText());
    }
}
