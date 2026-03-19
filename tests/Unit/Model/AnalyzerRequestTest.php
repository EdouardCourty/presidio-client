<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Enum\AllowListMatch;
use Ecourty\PresidioClient\Enum\EntityType;
use Ecourty\PresidioClient\Model\AdHocRecognizer;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnalyzerRequest::class)]
class AnalyzerRequestTest extends TestCase
{
    public function testToArrayMinimal(): void
    {
        $request = new AnalyzerRequest(text: 'Hello world');

        $array = $request->toArray();

        self::assertSame('Hello world', $array['text']);
        self::assertSame(AnalyzerRequest::DEFAULT_LANGUAGE, $array['language']);
        self::assertArrayNotHasKey('entities', $array);
        self::assertArrayNotHasKey('score_threshold', $array);
        self::assertArrayNotHasKey('context', $array);
        self::assertArrayNotHasKey('correlation_id', $array);
        self::assertArrayNotHasKey('allow_list', $array);
        self::assertArrayNotHasKey('allow_list_match', $array);
        self::assertArrayNotHasKey('regex_flags', $array);
        self::assertArrayNotHasKey('ad_hoc_recognizers', $array);
        self::assertArrayNotHasKey('return_decision_process', $array);
    }

    public function testToArrayFull(): void
    {
        $request = new AnalyzerRequest(
            text: 'My email is john@example.com',
            language: 'en',
            entities: [EntityType::EMAIL_ADDRESS, EntityType::PHONE_NUMBER],
            scoreThreshold: 0.5,
            context: ['email', 'address'],
        );

        $array = $request->toArray();

        self::assertSame('My email is john@example.com', $array['text']);
        self::assertSame('en', $array['language']);
        self::assertSame(['EMAIL_ADDRESS', 'PHONE_NUMBER'], $array['entities']);
        self::assertSame(0.5, $array['score_threshold']);
        self::assertSame(['email', 'address'], $array['context']);
    }

    public function testGetters(): void
    {
        $request = new AnalyzerRequest(
            text: 'test',
            language: 'fr',
            entities: [EntityType::PERSON],
            scoreThreshold: 0.8,
            context: ['driver', 'license'],
        );

        self::assertSame('test', $request->getText());
        self::assertSame('fr', $request->getLanguage());
        self::assertSame([EntityType::PERSON], $request->getEntities());
        self::assertSame(0.8, $request->getScoreThreshold());
        self::assertSame(['driver', 'license'], $request->getContext());
    }

    public function testToArrayWithAllowList(): void
    {
        $request = new AnalyzerRequest(
            text: 'John works at Acme Corp',
            allowList: ['Acme Corp'],
            allowListMatch: AllowListMatch::EXACT,
        );

        $array = $request->toArray();

        self::assertSame(['Acme Corp'], $array['allow_list']);
        self::assertSame('exact', $array['allow_list_match']);
    }

    public function testToArrayWithRegexAllowList(): void
    {
        $request = new AnalyzerRequest(
            text: 'Dr. Smith is here',
            allowList: ['^Dr\.$', '^Mr\.$'],
            allowListMatch: AllowListMatch::REGEX,
            regexFlags: 34,
        );

        $array = $request->toArray();

        self::assertSame(['^Dr\.$', '^Mr\.$'], $array['allow_list']);
        self::assertSame('regex', $array['allow_list_match']);
        self::assertSame(34, $array['regex_flags']);
    }

    public function testToArrayWithCorrelationId(): void
    {
        $request = new AnalyzerRequest(
            text: 'test',
            correlationId: '123e4567-e89b-12d3-a456-426614174000',
        );

        $array = $request->toArray();

        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $array['correlation_id']);
    }

    public function testToArrayWithReturnDecisionProcess(): void
    {
        $request = new AnalyzerRequest(
            text: 'Call 555-123-4567',
            returnDecisionProcess: true,
        );

        $array = $request->toArray();

        self::assertTrue($array['return_decision_process']);
    }

    public function testToArrayWithAdHocRecognizers(): void
    {
        $recognizer = new AdHocRecognizer(
            name: 'Zip Recognizer',
            supportedLanguage: 'en',
            supportedEntity: 'ZIP',
            patterns: [new Pattern('zip', '\d{5}', 0.5)],
            context: ['zip', 'code'],
        );

        $request = new AnalyzerRequest(
            text: 'My zip is 12345',
            adHocRecognizers: [$recognizer],
        );

        $array = $request->toArray();

        /** @var list<array<string, mixed>> $adHocRecognizers */
        $adHocRecognizers = $array['ad_hoc_recognizers'];
        self::assertCount(1, $adHocRecognizers);
        self::assertSame('Zip Recognizer', $adHocRecognizers[0]['name']);
        self::assertSame('ZIP', $adHocRecognizers[0]['supported_entity']);
    }

    public function testNewGetters(): void
    {
        $recognizer = new AdHocRecognizer(
            name: 'Test',
            supportedLanguage: 'en',
            supportedEntity: 'TEST',
        );

        $request = new AnalyzerRequest(
            text: 'test',
            correlationId: 'abc-123',
            allowList: ['Smith'],
            allowListMatch: AllowListMatch::EXACT,
            regexFlags: 42,
            adHocRecognizers: [$recognizer],
            returnDecisionProcess: true,
        );

        self::assertSame('abc-123', $request->getCorrelationId());
        self::assertSame(['Smith'], $request->getAllowList());
        self::assertSame(AllowListMatch::EXACT, $request->getAllowListMatch());
        self::assertSame(42, $request->getRegexFlags());
        self::assertCount(1, $request->getAdHocRecognizers());
        self::assertTrue($request->getReturnDecisionProcess());
    }
}
