<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AnonymizedEntity
{
    /**
     * @param string $operator   The operator that was applied (e.g. "replace", "mask", "encrypt")
     * @param string $entityType The PII entity type (e.g. "PERSON", "EMAIL_ADDRESS")
     * @param int    $start      Start position in the anonymized text (inclusive)
     * @param int    $end        End position in the anonymized text (exclusive)
     * @param string $text       The replacement text (e.g. "<PERSON>", "****", encrypted value)
     */
    public function __construct(
        private string $operator,
        private string $entityType,
        private int $start,
        private int $end,
        private string $text,
    ) {
    }

    public function getOperator(): string
    {
        return $this->operator;
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

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array{operator: string, entity_type: string, start: int, end: int, text: string}
     */
    public function toArray(): array
    {
        return [
            'operator' => $this->operator,
            'entity_type' => $this->entityType,
            'start' => $this->start,
            'end' => $this->end,
            'text' => $this->text,
        ];
    }

    /**
     * @param array{operator: string, entity_type: string, start: int, end: int, text: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            operator: $data['operator'],
            entityType: $data['entity_type'],
            start: $data['start'],
            end: $data['end'],
            text: $data['text'],
        );
    }
}
