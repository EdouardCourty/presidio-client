<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AnalysisExplanation
{
    /**
     * @param string  $recognizer              Name of the recognizer that detected the entity
     * @param ?string $patternName             Name of the regex pattern that matched (null for non-pattern recognizers)
     * @param ?string $pattern                 The regex pattern that matched (null for non-pattern recognizers)
     * @param float   $originalScore           Initial confidence score before context enhancement
     * @param float   $score                   Final confidence score after all adjustments
     * @param ?string $textualExplanation      Human-readable explanation of the detection
     * @param float   $scoreContextImprovement Score increase due to supportive context words
     * @param string  $supportiveContextWord   The context word that boosted the score
     * @param ?float  $validationResult        Result of checksum/validation logic (null if not applicable)
     */
    public function __construct(
        private string $recognizer,
        private ?string $patternName,
        private ?string $pattern,
        private float $originalScore,
        private float $score,
        private ?string $textualExplanation,
        private float $scoreContextImprovement,
        private string $supportiveContextWord,
        private ?float $validationResult,
    ) {
    }

    public function getRecognizer(): string
    {
        return $this->recognizer;
    }

    public function getPatternName(): ?string
    {
        return $this->patternName;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getOriginalScore(): float
    {
        return $this->originalScore;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function getTextualExplanation(): ?string
    {
        return $this->textualExplanation;
    }

    public function getScoreContextImprovement(): float
    {
        return $this->scoreContextImprovement;
    }

    public function getSupportiveContextWord(): string
    {
        return $this->supportiveContextWord;
    }

    public function getValidationResult(): ?float
    {
        return $this->validationResult;
    }

    /**
     * @return array{recognizer: string, pattern_name: string|null, pattern: string|null, original_score: float, score: float, textual_explanation: string|null, score_context_improvement: float, supportive_context_word: string, validation_result: float|null}
     */
    public function toArray(): array
    {
        return [
            'recognizer' => $this->recognizer,
            'pattern_name' => $this->patternName,
            'pattern' => $this->pattern,
            'original_score' => $this->originalScore,
            'score' => $this->score,
            'textual_explanation' => $this->textualExplanation,
            'score_context_improvement' => $this->scoreContextImprovement,
            'supportive_context_word' => $this->supportiveContextWord,
            'validation_result' => $this->validationResult,
        ];
    }

    /**
     * @param array{recognizer: string, pattern_name?: string|null, pattern?: string|null, original_score?: float, score?: float, textual_explanation?: string|null, score_context_improvement?: float, supportive_context_word?: string, validation_result?: float|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            recognizer: $data['recognizer'],
            patternName: $data['pattern_name'] ?? null,
            pattern: $data['pattern'] ?? null,
            originalScore: $data['original_score'] ?? 0.0,
            score: $data['score'] ?? 0.0,
            textualExplanation: $data['textual_explanation'] ?? null,
            scoreContextImprovement: $data['score_context_improvement'] ?? 0.0,
            supportiveContextWord: $data['supportive_context_word'] ?? '',
            validationResult: $data['validation_result'] ?? null,
        );
    }
}
