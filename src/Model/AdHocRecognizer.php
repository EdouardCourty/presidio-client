<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AdHocRecognizer
{
    /**
     * @param string        $name              Unique name for this recognizer
     * @param string        $supportedLanguage Language code this recognizer supports (e.g. "en")
     * @param string        $supportedEntity   Entity type this recognizer detects (e.g. "ZIP_CODE")
     * @param list<Pattern> $patterns          Regex patterns used for detection (mutually exclusive with $denyList)
     * @param list<string>  $denyList          Exact words to flag as PII (mutually exclusive with $patterns)
     * @param list<string>  $context           Context words that boost detection confidence when found nearby
     */
    public function __construct(
        private string $name,
        private string $supportedLanguage,
        private string $supportedEntity,
        private array $patterns = [],
        private array $denyList = [],
        private array $context = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSupportedLanguage(): string
    {
        return $this->supportedLanguage;
    }

    public function getSupportedEntity(): string
    {
        return $this->supportedEntity;
    }

    /**
     * @return list<Pattern>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @return list<string>
     */
    public function getDenyList(): array
    {
        return $this->denyList;
    }

    /**
     * @return list<string>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'supported_language' => $this->supportedLanguage,
            'supported_entity' => $this->supportedEntity,
        ];

        if ($this->patterns !== []) {
            $data['patterns'] = array_map(
                static fn (Pattern $pattern): array => $pattern->toArray(),
                $this->patterns,
            );
        }

        if ($this->denyList !== []) {
            $data['deny_list'] = $this->denyList;
        }

        if ($this->context !== []) {
            $data['context'] = $this->context;
        }

        return $data;
    }

    /**
     * @param array{name: string, supported_language: string, supported_entity: string, patterns?: list<array{name: string, regex: string, score: float}>, deny_list?: list<string>, context?: list<string>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            supportedLanguage: $data['supported_language'],
            supportedEntity: $data['supported_entity'],
            patterns: array_map(
                static fn (array $pattern): Pattern => Pattern::fromArray($pattern),
                $data['patterns'] ?? [],
            ),
            denyList: $data['deny_list'] ?? [],
            context: $data['context'] ?? [],
        );
    }
}
