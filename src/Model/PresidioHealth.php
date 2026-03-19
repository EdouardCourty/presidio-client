<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class PresidioHealth
{
    /**
     * @param bool $analyzer   Whether the Presidio Analyzer service is healthy
     * @param bool $anonymizer Whether the Presidio Anonymizer service is healthy
     */
    public function __construct(
        private bool $analyzer,
        private bool $anonymizer,
    ) {
    }

    public function isAnalyzerHealthy(): bool
    {
        return $this->analyzer;
    }

    public function isAnonymizerHealthy(): bool
    {
        return $this->anonymizer;
    }

    public function isHealthy(): bool
    {
        return $this->analyzer && $this->anonymizer;
    }

    /**
     * @return array{analyzer: bool, anonymizer: bool}
     */
    public function toArray(): array
    {
        return [
            'analyzer' => $this->analyzer,
            'anonymizer' => $this->anonymizer,
        ];
    }

    /**
     * @param array{analyzer: bool, anonymizer: bool} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            analyzer: $data['analyzer'],
            anonymizer: $data['anonymizer'],
        );
    }
}
