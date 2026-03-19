<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class AnonymizeRequest
{
    /**
     * @param string                         $text            The original text to anonymize
     * @param list<AnalyzerResult>           $analyzerResults PII entities detected by the analyzer
     * @param array<string, OperatorConfig>  $anonymizers     Operator config per entity type (key = entity type or "DEFAULT")
     */
    public function __construct(
        private string $text,
        private array $analyzerResults,
        private array $anonymizers = [],
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return list<AnalyzerResult>
     */
    public function getAnalyzerResults(): array
    {
        return $this->analyzerResults;
    }

    /**
     * @return array<string, OperatorConfig>
     */
    public function getAnonymizers(): array
    {
        return $this->anonymizers;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'analyzer_results' => array_map(
                static fn (AnalyzerResult $result): array => $result->toArray(),
                $this->analyzerResults,
            ),
        ];

        if ($this->anonymizers !== []) {
            $anonymizers = [];
            foreach ($this->anonymizers as $entityType => $config) {
                $anonymizers[$entityType] = $config->toArray();
            }
            $data['anonymizers'] = $anonymizers;
        }

        return $data;
    }
}
