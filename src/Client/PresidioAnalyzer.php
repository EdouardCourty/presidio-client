<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Client;

use Ecourty\PresidioClient\Exception\ApiException;
use Ecourty\PresidioClient\Model\AnalyzerRequest;
use Ecourty\PresidioClient\Model\AnalyzerResult;
use Ecourty\PresidioClient\Model\RecognizerResult;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PresidioAnalyzer
{
    public const string DEFAULT_BASE_URL = 'http://localhost:5001';

    private const string ENDPOINT_ANALYZE = '/analyze';
    private const string ENDPOINT_RECOGNIZERS = '/recognizers';
    private const string ENDPOINT_SUPPORTED_ENTITIES = '/supportedentities';
    private const string ENDPOINT_HEALTH = '/health';

    private const int HTTP_OK = 200;

    private HttpClientInterface $httpClient;

    public function __construct(
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create(['base_uri' => $baseUrl]);
    }

    /**
     * @return list<AnalyzerResult>
     */
    public function analyze(AnalyzerRequest $request): array
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT_ANALYZE, [
            'json' => $request->toArray(),
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var list<array{entity_type: string, start: int, end: int, score: float}> $data */
        $data = $response->toArray();

        return array_map(
            static fn (array $item): AnalyzerResult => AnalyzerResult::fromArray($item),
            $data,
        );
    }

    /**
     * @return list<RecognizerResult>
     */
    public function getRecognizers(?string $language = null): array
    {
        $query = [];
        if ($language !== null) {
            $query['language'] = $language;
        }

        $response = $this->httpClient->request('GET', self::ENDPOINT_RECOGNIZERS, [
            'query' => $query,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var list<array{name: string, supported_entities: list<string>, supported_languages?: list<string>, supported_language?: list<string>}> $data */
        $data = $response->toArray();

        return array_map(
            static fn (array $item): RecognizerResult => RecognizerResult::fromArray($item),
            $data,
        );
    }

    /**
     * @return list<string>
     */
    public function getSupportedEntities(?string $language = null): array
    {
        $query = [];
        if ($language !== null) {
            $query['language'] = $language;
        }

        $response = $this->httpClient->request('GET', self::ENDPOINT_SUPPORTED_ENTITIES, [
            'query' => $query,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var list<string> $data */
        $data = $response->toArray();

        return $data;
    }

    public function health(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::ENDPOINT_HEALTH);

            return $response->getStatusCode() === self::HTTP_OK;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }
}
