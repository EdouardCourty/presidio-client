<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AnonymizeResult
{
    /**
     * @param string                 $text  The anonymized text with PII replaced
     * @param list<AnonymizedEntity> $items Details of each anonymized entity (operator, position, replacement)
     */
    public function __construct(
        private string $text,
        private array $items,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return list<AnonymizedEntity>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array{text: string, items: list<array{operator: string, entity_type: string, start: int, end: int, text: string}>}
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'items' => array_map(
                static fn (AnonymizedEntity $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }

    /**
     * @param array{text: string, items: list<array{operator: string, entity_type: string, start: int, end: int, text: string}>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
            items: array_map(
                static fn (array $item): AnonymizedEntity => AnonymizedEntity::fromArray($item),
                $data['items'],
            ),
        );
    }
}
