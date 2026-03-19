<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient;

use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Enum\AllowListMatch;
use Ecourty\PresidioClient\Enum\EntityType;
use Ecourty\PresidioClient\Model\AdHocRecognizer;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\AnonymizeResult;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeResult;
use Ecourty\PresidioClient\Model\OperatorConfig;
use Ecourty\PresidioClient\Model\PresidioHealth;
use Ecourty\PresidioClient\Model\RecognizerResult;

class Presidio
{
    private PresidioAnalyzer $analyzer;
    private PresidioAnonymizer $anonymizer;

    public function __construct(
        ?PresidioAnalyzer $analyzer = null,
        ?PresidioAnonymizer $anonymizer = null,
    ) {
        $this->analyzer = $analyzer ?? new PresidioAnalyzer();
        $this->anonymizer = $anonymizer ?? new PresidioAnonymizer();
    }

    // ──── Convenience methods ────

    /**
     * Analyze text for PII and anonymize it in a single call.
     *
     * @param list<EntityType>              $entities
     * @param list<string>                  $context
     * @param array<string, OperatorConfig> $operators
     * @param list<string>                  $allowList
     * @param list<AdHocRecognizer>         $adHocRecognizers
     */
    public function anonymize(
        string $text,
        string $language = AnalyzerRequest::DEFAULT_LANGUAGE,
        array $entities = [],
        ?float $scoreThreshold = null,
        array $context = [],
        array $operators = [],
        array $allowList = [],
        ?AllowListMatch $allowListMatch = null,
        array $adHocRecognizers = [],
    ): AnonymizeResult {
        $analyzerResults = $this->analyzer->analyze(new AnalyzerRequest(
            text: $text,
            language: $language,
            entities: $entities,
            scoreThreshold: $scoreThreshold,
            context: $context,
            allowList: $allowList,
            allowListMatch: $allowListMatch,
            adHocRecognizers: $adHocRecognizers,
        ));

        return $this->anonymizer->anonymize(new AnonymizeRequest(
            text: $text,
            analyzerResults: $analyzerResults,
            anonymizers: $operators,
        ));
    }

    public function health(): PresidioHealth
    {
        return new PresidioHealth(
            analyzer: $this->analyzer->health(),
            anonymizer: $this->anonymizer->health(),
        );
    }

    // ──── Proxy: Analyzer ────

    /**
     * @return list<AnalyzerResult>
     */
    public function analyze(AnalyzerRequest $request): array
    {
        return $this->analyzer->analyze($request);
    }

    /**
     * @return list<RecognizerResult>
     */
    public function getRecognizers(?string $language = null): array
    {
        return $this->analyzer->getRecognizers($language);
    }

    /**
     * @return list<string>
     */
    public function getSupportedEntities(?string $language = null): array
    {
        return $this->analyzer->getSupportedEntities($language);
    }

    // ──── Proxy: Anonymizer ────

    public function anonymizeRequest(AnonymizeRequest $request): AnonymizeResult
    {
        return $this->anonymizer->anonymize($request);
    }

    public function deanonymize(DeanonymizeRequest $request): DeanonymizeResult
    {
        return $this->anonymizer->deanonymize($request);
    }

    /**
     * @return list<string>
     */
    public function getAnonymizers(): array
    {
        return $this->anonymizer->getAnonymizers();
    }

    /**
     * @return list<string>
     */
    public function getDeanonymizers(): array
    {
        return $this->anonymizer->getDeanonymizers();
    }

    // ──── Client access ────

    public function getAnalyzer(): PresidioAnalyzer
    {
        return $this->analyzer;
    }

    public function getAnonymizer(): PresidioAnonymizer
    {
        return $this->anonymizer;
    }
}
