<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Model\AnalysisExplanation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnalysisExplanation::class)]
class AnalysisExplanationTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $explanation = new AnalysisExplanation(
            recognizer: 'PhoneRecognizer',
            patternName: 'US Phone Regex',
            pattern: '\d{3}-\d{3}-\d{4}',
            originalScore: 0.8,
            score: 0.95,
            textualExplanation: 'Pattern matched. Context word increased score.',
            scoreContextImprovement: 0.15,
            supportiveContextWord: 'call',
            validationResult: null,
        );

        self::assertSame('PhoneRecognizer', $explanation->getRecognizer());
        self::assertSame('US Phone Regex', $explanation->getPatternName());
        self::assertSame('\d{3}-\d{3}-\d{4}', $explanation->getPattern());
        self::assertSame(0.8, $explanation->getOriginalScore());
        self::assertSame(0.95, $explanation->getScore());
        self::assertSame('Pattern matched. Context word increased score.', $explanation->getTextualExplanation());
        self::assertSame(0.15, $explanation->getScoreContextImprovement());
        self::assertSame('call', $explanation->getSupportiveContextWord());
        self::assertNull($explanation->getValidationResult());
    }

    public function testToArray(): void
    {
        $explanation = new AnalysisExplanation(
            recognizer: 'EmailRecognizer',
            patternName: 'Email Pattern',
            pattern: '[^@]+@[^@]+',
            originalScore: 1.0,
            score: 1.0,
            textualExplanation: 'Pattern match',
            scoreContextImprovement: 0.0,
            supportiveContextWord: '',
            validationResult: 1.0,
        );

        self::assertSame([
            'recognizer' => 'EmailRecognizer',
            'pattern_name' => 'Email Pattern',
            'pattern' => '[^@]+@[^@]+',
            'original_score' => 1.0,
            'score' => 1.0,
            'textual_explanation' => 'Pattern match',
            'score_context_improvement' => 0.0,
            'supportive_context_word' => '',
            'validation_result' => 1.0,
        ], $explanation->toArray());
    }

    public function testFromArrayFull(): void
    {
        $explanation = AnalysisExplanation::fromArray([
            'recognizer' => 'PhoneRecognizer',
            'pattern_name' => 'US Phone',
            'pattern' => '\d{3}-\d{3}-\d{4}',
            'original_score' => 0.7,
            'score' => 0.9,
            'textual_explanation' => 'Matched',
            'score_context_improvement' => 0.2,
            'supportive_context_word' => 'phone',
            'validation_result' => 0.9,
        ]);

        self::assertSame('PhoneRecognizer', $explanation->getRecognizer());
        self::assertSame('US Phone', $explanation->getPatternName());
        self::assertSame(0.7, $explanation->getOriginalScore());
        self::assertSame(0.9, $explanation->getScore());
        self::assertSame(0.2, $explanation->getScoreContextImprovement());
        self::assertSame('phone', $explanation->getSupportiveContextWord());
        self::assertSame(0.9, $explanation->getValidationResult());
    }

    public function testFromArrayMinimal(): void
    {
        $explanation = AnalysisExplanation::fromArray([
            'recognizer' => 'SpacyRecognizer',
        ]);

        self::assertSame('SpacyRecognizer', $explanation->getRecognizer());
        self::assertNull($explanation->getPatternName());
        self::assertNull($explanation->getPattern());
        self::assertSame(0.0, $explanation->getOriginalScore());
        self::assertSame(0.0, $explanation->getScore());
        self::assertNull($explanation->getTextualExplanation());
        self::assertSame(0.0, $explanation->getScoreContextImprovement());
        self::assertSame('', $explanation->getSupportiveContextWord());
        self::assertNull($explanation->getValidationResult());
    }
}
