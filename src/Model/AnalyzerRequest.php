<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Model;

use Ecourty\PresidioClient\Enum\AllowListMatch;
use Ecourty\PresidioClient\Enum\EntityType;

final readonly class AnalyzerRequest
{
    public const string DEFAULT_LANGUAGE = 'en';

    /**
     * @param string                $text                  The text to analyze for PII
     * @param string                $language              Language of the text (ISO 639-1 code, e.g. "en")
     * @param list<EntityType>      $entities              Limit detection to these entity types (empty = all)
     * @param ?float                $scoreThreshold        Minimum confidence score to return a result (0.0–1.0)
     * @param list<string>          $context               Context words that boost detection confidence
     * @param ?string               $correlationId         Correlation ID for logging and tracing
     * @param list<string>          $allowList             Values to exclude from PII detection
     * @param ?AllowListMatch       $allowListMatch        How to match allow list values (exact or regex)
     * @param ?int                  $regexFlags            Python regex flags for pattern matching (e.g. re.DOTALL = 16)
     * @param list<AdHocRecognizer> $adHocRecognizers      Custom recognizers to use for this request only
     * @param ?bool                 $returnDecisionProcess If true, include analysis explanation in the response
     */
    public function __construct(
        private string $text,
        private string $language = self::DEFAULT_LANGUAGE,
        private array $entities = [],
        private ?float $scoreThreshold = null,
        private array $context = [],
        private ?string $correlationId = null,
        private array $allowList = [],
        private ?AllowListMatch $allowListMatch = null,
        private ?int $regexFlags = null,
        private array $adHocRecognizers = [],
        private ?bool $returnDecisionProcess = null,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return list<EntityType>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getScoreThreshold(): ?float
    {
        return $this->scoreThreshold;
    }

    /**
     * @return list<string>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    /**
     * @return list<string>
     */
    public function getAllowList(): array
    {
        return $this->allowList;
    }

    public function getAllowListMatch(): ?AllowListMatch
    {
        return $this->allowListMatch;
    }

    public function getRegexFlags(): ?int
    {
        return $this->regexFlags;
    }

    /**
     * @return list<AdHocRecognizer>
     */
    public function getAdHocRecognizers(): array
    {
        return $this->adHocRecognizers;
    }

    public function getReturnDecisionProcess(): ?bool
    {
        return $this->returnDecisionProcess;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'language' => $this->language,
        ];

        if ($this->entities !== []) {
            $data['entities'] = array_map(
                static fn (EntityType $entity): string => $entity->value,
                $this->entities,
            );
        }

        if ($this->scoreThreshold !== null) {
            $data['score_threshold'] = $this->scoreThreshold;
        }

        if ($this->context !== []) {
            $data['context'] = $this->context;
        }

        if ($this->correlationId !== null) {
            $data['correlation_id'] = $this->correlationId;
        }

        if ($this->allowList !== []) {
            $data['allow_list'] = $this->allowList;
        }

        if ($this->allowListMatch !== null) {
            $data['allow_list_match'] = $this->allowListMatch->value;
        }

        if ($this->regexFlags !== null) {
            $data['regex_flags'] = $this->regexFlags;
        }

        if ($this->adHocRecognizers !== []) {
            $data['ad_hoc_recognizers'] = array_map(
                static fn (AdHocRecognizer $recognizer): array => $recognizer->toArray(),
                $this->adHocRecognizers,
            );
        }

        if ($this->returnDecisionProcess !== null) {
            $data['return_decision_process'] = $this->returnDecisionProcess;
        }

        return $data;
    }
}
