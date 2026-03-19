<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class Pattern
{
    /**
     * @param string $name  Descriptive name for this pattern (e.g. "US zip code")
     * @param string $regex The regex pattern to match (Python regex syntax)
     * @param float  $score Base confidence score when this pattern matches (0.0–1.0)
     */
    public function __construct(
        private string $name,
        private string $regex,
        private float $score,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return array{name: string, regex: string, score: float}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'regex' => $this->regex,
            'score' => $this->score,
        ];
    }

    /**
     * @param array{name: string, regex: string, score: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            regex: $data['regex'],
            score: $data['score'],
        );
    }
}
