<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class RecognizerResult
{
    /**
     * @param string       $name               Recognizer name (e.g. "SpacyRecognizer", "EmailRecognizer")
     * @param list<string> $supportedEntities  Entity types this recognizer can detect (e.g. ["PERSON", "LOCATION"])
     * @param list<string> $supportedLanguages Language codes this recognizer supports (e.g. ["en"])
     */
    public function __construct(
        private string $name,
        private array $supportedEntities,
        private array $supportedLanguages,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<string>
     */
    public function getSupportedEntities(): array
    {
        return $this->supportedEntities;
    }

    /**
     * @return list<string>
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * @return array{name: string, supported_entities: list<string>, supported_languages: list<string>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'supported_entities' => $this->supportedEntities,
            'supported_languages' => $this->supportedLanguages,
        ];
    }

    /**
     * @param array{name: string, supported_entities: list<string>, supported_languages?: list<string>, supported_language?: list<string>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            supportedEntities: $data['supported_entities'],
            supportedLanguages: $data['supported_languages'] ?? $data['supported_language'] ?? [],
        );
    }
}
