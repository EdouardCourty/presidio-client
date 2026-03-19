<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\AnalysisExplanation;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\AnonymizedEntity;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\AnonymizeResult;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeResult;
use Ecourty\PresidioClient\Model\OperatorConfig;
use Ecourty\PresidioClient\Model\RecognizerResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnalysisExplanation::class)]
#[CoversClass(AnalyzerResult::class)]
#[CoversClass(AnonymizedEntity::class)]
#[CoversClass(AnonymizeRequest::class)]
#[CoversClass(AnonymizeResult::class)]
#[CoversClass(DeanonymizeRequest::class)]
#[CoversClass(DeanonymizeResult::class)]
#[CoversClass(OperatorConfig::class)]
#[CoversClass(RecognizerResult::class)]
class ModelsTest extends TestCase
{
    public function testAnalyzerResultFromArray(): void
    {
        $result = AnalyzerResult::fromArray([
            'entity_type' => 'EMAIL_ADDRESS',
            'start' => 10,
            'end' => 25,
            'score' => 0.95,
        ]);

        self::assertSame('EMAIL_ADDRESS', $result->getEntityType());
        self::assertSame(10, $result->getStart());
        self::assertSame(25, $result->getEnd());
        self::assertSame(0.95, $result->getScore());
        self::assertSame([], $result->getRecognitionMetadata());
        self::assertNull($result->getAnalysisExplanation());
    }

    public function testAnalyzerResultFromArrayWithMetadata(): void
    {
        $result = AnalyzerResult::fromArray([
            'entity_type' => 'PHONE_NUMBER',
            'start' => 22,
            'end' => 34,
            'score' => 0.95,
            'recognition_metadata' => [
                'recognizer_name' => 'PhoneRecognizer',
            ],
            'analysis_explanation' => [
                'recognizer' => 'PhoneRecognizer',
                'pattern_name' => 'US Phone Regex',
                'pattern' => '\d{3}-\d{3}-\d{4}',
                'original_score' => 0.8,
                'score' => 0.95,
                'textual_explanation' => 'Pattern matched.',
                'score_context_improvement' => 0.15,
                'supportive_context_word' => 'call',
                'validation_result' => null,
            ],
        ]);

        self::assertSame('PHONE_NUMBER', $result->getEntityType());
        self::assertSame(['recognizer_name' => 'PhoneRecognizer'], $result->getRecognitionMetadata());
        self::assertNotNull($result->getAnalysisExplanation());
        self::assertSame('PhoneRecognizer', $result->getAnalysisExplanation()->getRecognizer());
        self::assertSame('US Phone Regex', $result->getAnalysisExplanation()->getPatternName());
        self::assertSame(0.15, $result->getAnalysisExplanation()->getScoreContextImprovement());
    }

    public function testAnalyzerResultToArrayWithMetadata(): void
    {
        $explanation = new AnalysisExplanation(
            recognizer: 'EmailRecognizer',
            patternName: null,
            pattern: null,
            originalScore: 1.0,
            score: 1.0,
            textualExplanation: null,
            scoreContextImprovement: 0.0,
            supportiveContextWord: '',
            validationResult: null,
        );

        $result = new AnalyzerResult(
            'EMAIL_ADDRESS', 10, 25, 1.0,
            ['recognizer_name' => 'EmailRecognizer'],
            $explanation,
        );

        $array = $result->toArray();

        self::assertSame('EMAIL_ADDRESS', $array['entity_type']);
        self::assertSame(['recognizer_name' => 'EmailRecognizer'], $array['recognition_metadata']);

        /** @var array{recognizer: string} $analysisExplanation */
        $analysisExplanation = $array['analysis_explanation'];
        self::assertSame('EmailRecognizer', $analysisExplanation['recognizer']);
    }

    public function testAnalyzerResultToArray(): void
    {
        $result = new AnalyzerResult('PERSON', 0, 8, 0.85);

        self::assertSame([
            'entity_type' => 'PERSON',
            'start' => 0,
            'end' => 8,
            'score' => 0.85,
        ], $result->toArray());
    }

    public function testAnonymizedEntityToArray(): void
    {
        $entity = new AnonymizedEntity('replace', 'EMAIL_ADDRESS', 5, 20, '<EMAIL_ADDRESS>');

        self::assertSame([
            'operator' => 'replace',
            'entity_type' => 'EMAIL_ADDRESS',
            'start' => 5,
            'end' => 20,
            'text' => '<EMAIL_ADDRESS>',
        ], $entity->toArray());
    }

    public function testAnonymizedEntityFromArray(): void
    {
        $entity = AnonymizedEntity::fromArray([
            'operator' => 'replace',
            'entity_type' => 'EMAIL_ADDRESS',
            'start' => 5,
            'end' => 20,
            'text' => '<EMAIL_ADDRESS>',
        ]);

        self::assertSame('replace', $entity->getOperator());
        self::assertSame('EMAIL_ADDRESS', $entity->getEntityType());
        self::assertSame(5, $entity->getStart());
        self::assertSame(20, $entity->getEnd());
        self::assertSame('<EMAIL_ADDRESS>', $entity->getText());
    }

    public function testAnonymizeResultFromArray(): void
    {
        $result = AnonymizeResult::fromArray([
            'text' => 'Hello <PERSON>',
            'items' => [
                [
                    'operator' => 'replace',
                    'entity_type' => 'PERSON',
                    'start' => 6,
                    'end' => 14,
                    'text' => '<PERSON>',
                ],
            ],
        ]);

        self::assertSame('Hello <PERSON>', $result->getText());
        self::assertCount(1, $result->getItems());
        self::assertSame('PERSON', $result->getItems()[0]->getEntityType());
    }

    public function testAnonymizeResultToArray(): void
    {
        $result = AnonymizeResult::fromArray([
            'text' => 'Hello <PERSON>',
            'items' => [
                [
                    'operator' => 'replace',
                    'entity_type' => 'PERSON',
                    'start' => 6,
                    'end' => 14,
                    'text' => '<PERSON>',
                ],
            ],
        ]);

        self::assertSame([
            'text' => 'Hello <PERSON>',
            'items' => [
                [
                    'operator' => 'replace',
                    'entity_type' => 'PERSON',
                    'start' => 6,
                    'end' => 14,
                    'text' => '<PERSON>',
                ],
            ],
        ], $result->toArray());
    }

    public function testDeanonymizeResultFromArray(): void
    {
        $result = DeanonymizeResult::fromArray(['text' => 'Hello John']);

        self::assertSame('Hello John', $result->getText());
    }

    public function testDeanonymizeResultToArray(): void
    {
        $result = DeanonymizeResult::fromArray(['text' => 'Hello John']);

        self::assertSame(['text' => 'Hello John'], $result->toArray());
    }

    public function testAnonymizeRequestToArray(): void
    {
        $request = new AnonymizeRequest(
            text: 'Hello John',
            analyzerResults: [
                new AnalyzerResult('PERSON', 6, 10, 0.9),
            ],
            anonymizers: [
                'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => 'REDACTED']),
            ],
        );

        $array = $request->toArray();

        self::assertSame('Hello John', $array['text']);

        /** @var list<array<string, mixed>> $analyzerResults */
        $analyzerResults = $array['analyzer_results'];
        self::assertCount(1, $analyzerResults);
        self::assertSame('PERSON', $analyzerResults[0]['entity_type']);
        self::assertArrayHasKey('anonymizers', $array);

        /** @var array<string, array<string, mixed>> $anonymizers */
        $anonymizers = $array['anonymizers'];
        self::assertSame(['type' => 'replace', 'new_value' => 'REDACTED'], $anonymizers['PERSON']);
    }

    public function testAnonymizeRequestGetters(): void
    {
        $analyzerResult = new AnalyzerResult('PERSON', 6, 10, 0.9);
        $anonymizer = new OperatorConfig(OperatorType::REDACT);

        $request = new AnonymizeRequest(
            text: 'Hello John',
            analyzerResults: [$analyzerResult],
            anonymizers: ['PERSON' => $anonymizer],
        );

        self::assertSame('Hello John', $request->getText());
        self::assertSame([$analyzerResult], $request->getAnalyzerResults());
        self::assertSame(['PERSON' => $anonymizer], $request->getAnonymizers());
    }

    public function testDeanonymizeRequestToArray(): void
    {
        $entity = new AnonymizedEntity('replace', 'PERSON', 6, 14, '<PERSON>');

        $request = new DeanonymizeRequest(
            text: 'Hello <PERSON>',
            anonymizerResults: [$entity],
        );

        $array = $request->toArray();

        self::assertSame('Hello <PERSON>', $array['text']);

        /** @var list<array<string, mixed>> $anonymizerResults */
        $anonymizerResults = $array['anonymizer_results'];
        self::assertCount(1, $anonymizerResults);
        self::assertSame('replace', $anonymizerResults[0]['operator']);
    }

    public function testDeanonymizeRequestGetters(): void
    {
        $entity = new AnonymizedEntity('replace', 'PERSON', 6, 14, '<PERSON>');

        $request = new DeanonymizeRequest(
            text: 'Hello <PERSON>',
            anonymizerResults: [$entity],
        );

        self::assertSame('Hello <PERSON>', $request->getText());
        self::assertSame([$entity], $request->getAnonymizerResults());
        self::assertSame([], $request->getDeanonymizers());
    }

    public function testOperatorConfigToArray(): void
    {
        $config = new OperatorConfig(OperatorType::MASK, ['masking_char' => '*', 'chars_to_mask' => 5]);

        self::assertSame(OperatorType::MASK, $config->getType());
        self::assertSame(['masking_char' => '*', 'chars_to_mask' => 5], $config->getParams());
        self::assertSame(['type' => 'mask', 'masking_char' => '*', 'chars_to_mask' => 5], $config->toArray());
    }

    public function testOperatorConfigWithoutParams(): void
    {
        $config = new OperatorConfig(OperatorType::REDACT);

        self::assertSame(['type' => 'redact'], $config->toArray());
    }

    public function testOperatorConfigFromArray(): void
    {
        $config = OperatorConfig::fromArray([
            'type' => 'mask',
            'masking_char' => '*',
            'chars_to_mask' => 5,
        ]);

        self::assertSame(OperatorType::MASK, $config->getType());
        self::assertSame(['masking_char' => '*', 'chars_to_mask' => 5], $config->getParams());
    }

    public function testOperatorConfigFromArrayWithoutParams(): void
    {
        $config = OperatorConfig::fromArray(['type' => 'redact']);

        self::assertSame(OperatorType::REDACT, $config->getType());
        self::assertSame([], $config->getParams());
    }

    public function testOperatorConfigRoundTrip(): void
    {
        $original = new OperatorConfig(OperatorType::HASH, ['hash_type' => 'sha256']);
        $restored = OperatorConfig::fromArray($original->toArray());

        self::assertSame($original->getType(), $restored->getType());
        self::assertSame($original->getParams(), $restored->getParams());
    }

    public function testRecognizerResultFromArray(): void
    {
        $result = RecognizerResult::fromArray([
            'name' => 'CreditCardRecognizer',
            'supported_entities' => ['CREDIT_CARD'],
            'supported_languages' => ['en', 'fr'],
        ]);

        self::assertSame('CreditCardRecognizer', $result->getName());
        self::assertSame(['CREDIT_CARD'], $result->getSupportedEntities());
        self::assertSame(['en', 'fr'], $result->getSupportedLanguages());
    }

    public function testRecognizerResultToArray(): void
    {
        $result = RecognizerResult::fromArray([
            'name' => 'CreditCardRecognizer',
            'supported_entities' => ['CREDIT_CARD'],
            'supported_languages' => ['en', 'fr'],
        ]);

        self::assertSame([
            'name' => 'CreditCardRecognizer',
            'supported_entities' => ['CREDIT_CARD'],
            'supported_languages' => ['en', 'fr'],
        ], $result->toArray());
    }
}
