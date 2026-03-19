<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AnalyzerResult
{
    /**
     * @param string               $entityType          The detected PII entity type (e.g. "PERSON", "EMAIL_ADDRESS")
     * @param int                  $start               Start position of the entity in the text (inclusive)
     * @param int                  $end                 End position of the entity in the text (exclusive)
     * @param float                $score               Confidence score of the detection (0.0–1.0)
     * @param array<string, mixed> $recognitionMetadata Additional metadata from the recognizer (e.g. recognizer_name)
     * @param ?AnalysisExplanation $analysisExplanation Detailed explanation of the detection (when returnDecisionProcess is true)
     */
    public function __construct(
        private string $entityType,
        private int $start,
        private int $end,
        private float $score,
        private array $recognitionMetadata = [],
        private ?AnalysisExplanation $analysisExplanation = null,
    ) {
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRecognitionMetadata(): array
    {
        return $this->recognitionMetadata;
    }

    public function getAnalysisExplanation(): ?AnalysisExplanation
    {
        return $this->analysisExplanation;
    }

    /**
     * @param array{entity_type: string, start: int, end: int, score: float, recognition_metadata?: array<string, mixed>, analysis_explanation?: array{recognizer: string, pattern_name?: string|null, pattern?: string|null, original_score?: float, score?: float, textual_explanation?: string|null, score_context_improvement?: float, supportive_context_word?: string, validation_result?: float|null}} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entityType: $data['entity_type'],
            start: $data['start'],
            end: $data['end'],
            score: $data['score'],
            recognitionMetadata: $data['recognition_metadata'] ?? [],
            analysisExplanation: isset($data['analysis_explanation'])
                ? AnalysisExplanation::fromArray($data['analysis_explanation'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'entity_type' => $this->entityType,
            'start' => $this->start,
            'end' => $this->end,
            'score' => $this->score,
        ];

        if ($this->recognitionMetadata !== []) {
            $data['recognition_metadata'] = $this->recognitionMetadata;
        }

        if ($this->analysisExplanation !== null) {
            $data['analysis_explanation'] = $this->analysisExplanation->toArray();
        }

        return $data;
    }
}
