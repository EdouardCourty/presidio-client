<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Client;

use Ecourty\PresidioClient\Exception\ApiException;
use Ecourty\PresidioClient\Model\AnonymizeRequest;
use Ecourty\PresidioClient\Model\AnonymizeResult;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\DeanonymizeResult;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PresidioAnonymizer
{
    public const string DEFAULT_BASE_URL = 'http://localhost:5002';

    private const string ENDPOINT_ANONYMIZE = '/anonymize';
    private const string ENDPOINT_DEANONYMIZE = '/deanonymize';
    private const string ENDPOINT_ANONYMIZERS = '/anonymizers';
    private const string ENDPOINT_DEANONYMIZERS = '/deanonymizers';
    private const string ENDPOINT_HEALTH = '/health';

    private const int HTTP_OK = 200;

    private HttpClientInterface $httpClient;

    public function __construct(
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create(['base_uri' => $baseUrl]);
    }

    public function anonymize(AnonymizeRequest $request): AnonymizeResult
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT_ANONYMIZE, [
            'json' => $request->toArray(),
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var array{text: string, items: list<array{operator: string, entity_type: string, start: int, end: int, text: string}>} $data */
        $data = $response->toArray();

        return AnonymizeResult::fromArray($data);
    }

    public function deanonymize(DeanonymizeRequest $request): DeanonymizeResult
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT_DEANONYMIZE, [
            'json' => $request->toArray(),
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var array{text: string} $data */
        $data = $response->toArray();

        return DeanonymizeResult::fromArray($data);
    }

    /**
     * @return list<string>
     */
    public function getAnonymizers(): array
    {
        $response = $this->httpClient->request('GET', self::ENDPOINT_ANONYMIZERS);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_OK) {
            throw new ApiException($statusCode, $response->getContent(false));
        }

        /** @var list<string> $data */
        $data = $response->toArray();

        return $data;
    }

    /**
     * @return list<string>
     */
    public function getDeanonymizers(): array
    {
        $response = $this->httpClient->request('GET', self::ENDPOINT_DEANONYMIZERS);

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
        $response = $this->httpClient->request('GET', self::ENDPOINT_HEALTH);

        return $response->getStatusCode() === self::HTTP_OK;
    }
}
