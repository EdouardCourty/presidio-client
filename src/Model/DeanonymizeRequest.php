<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

final readonly class DeanonymizeRequest
{
    /**
     * @param string                        $text              The anonymized text to reverse
     * @param list<AnonymizedEntity>        $anonymizerResults Entities from the anonymization result (AnonymizeResult::getItems())
     * @param array<string, OperatorConfig> $deanonymizers     Deanonymizer config per entity type (key = entity type or "DEFAULT")
     */
    public function __construct(
        private string $text,
        private array $anonymizerResults,
        private array $deanonymizers = [],
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return list<AnonymizedEntity>
     */
    public function getAnonymizerResults(): array
    {
        return $this->anonymizerResults;
    }

    /**
     * @return array<string, OperatorConfig>
     */
    public function getDeanonymizers(): array
    {
        return $this->deanonymizers;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'anonymizer_results' => array_map(
                static fn (AnonymizedEntity $entity): array => $entity->toArray(),
                $this->anonymizerResults,
            ),
        ];

        if ($this->deanonymizers !== []) {
            $deanonymizers = [];
            foreach ($this->deanonymizers as $entityType => $config) {
                $deanonymizers[$entityType] = $config->toArray();
            }
            $data['deanonymizers'] = $deanonymizers;
        }

        return $data;
    }
}
