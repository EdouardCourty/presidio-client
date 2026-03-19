<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Integration\Client;

use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Enum\EntityType;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\RecognizerResult;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests requiring running Presidio services.
 * Start them with: docker compose up -d
 *
 * Run with: composer test-integration
 */
class PresidioAnalyzerIntegrationTest extends TestCase
{
    private PresidioAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new PresidioAnalyzer('http://localhost:5001');

        if (!$this->analyzer->health()) {
            self::markTestSkipped('Presidio Analyzer is not running on http://localhost:5001');
        }
    }

    public function testHealth(): void
    {
        self::assertTrue($this->analyzer->health());
    }

    public function testAnalyzeDetectsEmail(): void
    {
        $request = new AnalyzerRequest(
            text: 'Contact me at john.doe@example.com',
            entities: [EntityType::EMAIL_ADDRESS],
        );

        $results = $this->analyzer->analyze($request);

        self::assertNotEmpty($results);
        self::assertSame('EMAIL_ADDRESS', $results[0]->getEntityType());
        self::assertSame(14, $results[0]->getStart());
        self::assertSame(34, $results[0]->getEnd());
    }

    public function testAnalyzeDetectsMultipleEntities(): void
    {
        $request = new AnalyzerRequest(
            text: 'My name is John Smith, my email is john@example.com and my phone is 555-123-4567',
        );

        $results = $this->analyzer->analyze($request);

        $entityTypes = array_map(
            static fn ($r): string => $r->getEntityType(),
            $results,
        );

        self::assertContains('EMAIL_ADDRESS', $entityTypes);
        self::assertContains('PERSON', $entityTypes);
        self::assertContains('PHONE_NUMBER', $entityTypes);
    }

    public function testAnalyzeWithScoreThreshold(): void
    {
        $request = new AnalyzerRequest(
            text: 'My name is John Smith, my email is john@example.com and my phone is 555-123-4567',
            scoreThreshold: 0.9,
        );

        $results = $this->analyzer->analyze($request);

        foreach ($results as $result) {
            self::assertGreaterThanOrEqual(0.9, $result->getScore());
        }
    }

    public function testAnalyzeReturnsEmptyForCleanText(): void
    {
        $request = new AnalyzerRequest(
            text: 'The weather is nice today.',
            entities: [EntityType::CREDIT_CARD, EntityType::US_SSN],
        );

        $results = $this->analyzer->analyze($request);

        self::assertSame([], $results);
    }

    public function testGetSupportedEntities(): void
    {
        $entities = $this->analyzer->getSupportedEntities();

        self::assertNotEmpty($entities);
        self::assertContains('EMAIL_ADDRESS', $entities);
        self::assertContains('PHONE_NUMBER', $entities);
        self::assertContains('PERSON', $entities);
    }

    public function testGetRecognizers(): void
    {
        $recognizers = $this->analyzer->getRecognizers();

        self::assertNotEmpty($recognizers);

        $names = array_map(
            static fn (RecognizerResult $r): string => $r->getName(),
            $recognizers,
        );

        self::assertContains('EmailRecognizer', $names);
    }
}
