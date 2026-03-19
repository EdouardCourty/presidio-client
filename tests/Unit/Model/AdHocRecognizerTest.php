<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Model\AdHocRecognizer;
use Ecourty\PresidioClient\Model\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdHocRecognizer::class)]
#[CoversClass(Pattern::class)]
class AdHocRecognizerTest extends TestCase
{
    public function testPatternBasedRecognizer(): void
    {
        $pattern = new Pattern(
            name: 'zip code (weak)',
            regex: '(\b\d{5}(?:\-\d{4})?\b)',
            score: 0.01,
        );

        $recognizer = new AdHocRecognizer(
            name: 'Zip code Recognizer',
            supportedLanguage: 'en',
            supportedEntity: 'ZIP',
            patterns: [$pattern],
            context: ['zip', 'code'],
        );

        self::assertSame('Zip code Recognizer', $recognizer->getName());
        self::assertSame('en', $recognizer->getSupportedLanguage());
        self::assertSame('ZIP', $recognizer->getSupportedEntity());
        self::assertCount(1, $recognizer->getPatterns());
        self::assertSame([], $recognizer->getDenyList());
        self::assertSame(['zip', 'code'], $recognizer->getContext());
    }

    public function testDenyListBasedRecognizer(): void
    {
        $recognizer = new AdHocRecognizer(
            name: 'Mr. Recognizer',
            supportedLanguage: 'en',
            supportedEntity: 'MR_TITLE',
            denyList: ['Mr', 'Mr.', 'Mister'],
        );

        self::assertSame('Mr. Recognizer', $recognizer->getName());
        self::assertSame(['Mr', 'Mr.', 'Mister'], $recognizer->getDenyList());
        self::assertSame([], $recognizer->getPatterns());
        self::assertSame([], $recognizer->getContext());
    }

    public function testToArrayWithPatterns(): void
    {
        $recognizer = new AdHocRecognizer(
            name: 'Zip Recognizer',
            supportedLanguage: 'en',
            supportedEntity: 'ZIP',
            patterns: [new Pattern('zip', '\d{5}', 0.5)],
            context: ['zip'],
        );

        $array = $recognizer->toArray();

        self::assertSame('Zip Recognizer', $array['name']);
        self::assertSame('en', $array['supported_language']);
        self::assertSame('ZIP', $array['supported_entity']);

        /** @var list<array{name: string, regex: string, score: float}> $patterns */
        $patterns = $array['patterns'];
        self::assertCount(1, $patterns);
        self::assertSame('zip', $patterns[0]['name']);
        self::assertSame(['zip'], $array['context']);
        self::assertArrayNotHasKey('deny_list', $array);
    }

    public function testToArrayWithDenyList(): void
    {
        $recognizer = new AdHocRecognizer(
            name: 'Title Recognizer',
            supportedLanguage: 'en',
            supportedEntity: 'TITLE',
            denyList: ['Mr', 'Mrs'],
        );

        $array = $recognizer->toArray();

        self::assertSame(['Mr', 'Mrs'], $array['deny_list']);
        self::assertArrayNotHasKey('patterns', $array);
        self::assertArrayNotHasKey('context', $array);
    }

    public function testFromArrayWithPatterns(): void
    {
        $recognizer = AdHocRecognizer::fromArray([
            'name' => 'Zip Recognizer',
            'supported_language' => 'en',
            'supported_entity' => 'ZIP',
            'patterns' => [
                ['name' => 'zip', 'regex' => '\d{5}', 'score' => 0.5],
            ],
            'context' => ['zip', 'code'],
        ]);

        self::assertSame('Zip Recognizer', $recognizer->getName());
        self::assertCount(1, $recognizer->getPatterns());
        self::assertSame('zip', $recognizer->getPatterns()[0]->getName());
        self::assertSame(['zip', 'code'], $recognizer->getContext());
    }

    public function testFromArrayWithDenyList(): void
    {
        $recognizer = AdHocRecognizer::fromArray([
            'name' => 'Title Recognizer',
            'supported_language' => 'en',
            'supported_entity' => 'TITLE',
            'deny_list' => ['Mr', 'Mrs'],
        ]);

        self::assertSame(['Mr', 'Mrs'], $recognizer->getDenyList());
        self::assertSame([], $recognizer->getPatterns());
    }

    public function testFromArrayMinimal(): void
    {
        $recognizer = AdHocRecognizer::fromArray([
            'name' => 'Test',
            'supported_language' => 'en',
            'supported_entity' => 'TEST',
        ]);

        self::assertSame('Test', $recognizer->getName());
        self::assertSame([], $recognizer->getPatterns());
        self::assertSame([], $recognizer->getDenyList());
        self::assertSame([], $recognizer->getContext());
    }
}
