<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class DeanonymizeResult
{
    /**
     * @param string $text The deanonymized (restored) text
     */
    public function __construct(
        private string $text,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array{text: string}
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
        ];
    }

    /**
     * @param array{text: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
        );
    }
}
