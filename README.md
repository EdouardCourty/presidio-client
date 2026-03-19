# Presidio Client for PHP

A typed PHP client for [Microsoft Presidio](https://github.com/microsoft/presidio) — detect and anonymize PII (Personally Identifiable Information) in text.

## Requirements

- PHP 8.3+
- A running [Microsoft Presidio](https://github.com/microsoft/presidio) instance (Analyzer + Anonymizer services)

## Installation

```bash
composer require ecourty/presidio-client
```

## Quick Start

The `Presidio` facade is the simplest way to use the library — it combines both clients and provides convenience methods:

```php
use Ecourty\PresidioClient\Presidio;

// Use with default configuration (localhost:5001 for Analyzer, localhost:5002 for Anonymizer)
$presidio = new Presidio();

// Use with custom base URLs
$presidio = new Presidio(
    analyzer: new PresidioAnalyzer('http://presidio-analyzer:5001'),
    anonymizer: new PresidioAnonymizer('http://presidio-anonymizer:5002'),
);

// Analyze + Anonymize in a single call
$result = $presidio->anonymize('My email is john@example.com and my name is John Smith');

echo $result->getText();
// My email is <EMAIL_ADDRESS> and my name is <PERSON>
```

### Custom Operators

You can configure different anonymization strategies per entity type:

```php
use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\OperatorConfig;

$result = $presidio->anonymize(
    text: 'My name is John Smith, email: john@example.com',
    operators: [
        'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => '[REDACTED]']),
        'EMAIL_ADDRESS' => new OperatorConfig(OperatorType::MASK, [
            'masking_char' => '*',
            'chars_to_mask' => 12,
            'from_end' => false,
        ]),
    ],
);
```

Available operators: `replace`, `redact`, `hash`, `mask`, `encrypt`, `decrypt`, `custom`, `keep`.  
Check the [OperatorType enum](src/Enum/OperatorType.php) for details.

### Encrypt & Decrypt (Round-Trip)

```php
use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\OperatorConfig;

$key = 'aaaaaaaaaaaaaaaa'; // 128-bit AES key

// Encrypt PII
$anonymized = $presidio->anonymize(
    text: 'My name is John Smith',
    operators: [
        'DEFAULT' => new OperatorConfig(OperatorType::ENCRYPT, ['key' => $key]),
    ],
);

// Decrypt PII
$deanonymized = $presidio->deanonymize(new DeanonymizeRequest(
    text: $anonymized->getText(),
    anonymizerResults: $anonymized->getItems(),
    deanonymizers: [
        'DEFAULT' => new OperatorConfig(OperatorType::DECRYPT, ['key' => $key]),
    ],
));

echo $deanonymized->getText();
// My name is John Smith
```

### Filter by Entity Type

```php
use Ecourty\PresidioClient\Enum\EntityType;

$result = $presidio->anonymize(
    text: 'Contact john@example.com or call 555-123-4567',
    entities: [EntityType::EMAIL_ADDRESS], // only detect emails
    scoreThreshold: 0.8,
);
```

### Allow List

Exclude specific values from being detected as PII:

```php
use Ecourty\PresidioClient\Enum\AllowListMatch;

// "Acme Corp" will not be detected as an organization, but "John" will still be detected as a person.
$result = $presidio->anonymize(
    text: 'Contact John at Acme Corp',
    allowList: ['Acme Corp'],
    allowListMatch: AllowListMatch::EXACT,
);
```

### Ad-Hoc Recognizers

Add custom recognizers on the fly without modifying the Presidio server:

```php
use Ecourty\PresidioClient\Model\AdHocRecognizer;
use Ecourty\PresidioClient\Model\Pattern;

// Pattern-based recognizer
$zipRecognizer = new AdHocRecognizer(
    name: 'Zip code Recognizer',
    supportedLanguage: 'en',
    supportedEntity: 'ZIP',
    patterns: [new Pattern('zip code (weak)', '(\b\d{5}(?:\-\d{4})?\b)', 0.01)],
    context: ['zip', 'code'],
);

// Deny-list based recognizer
$titleRecognizer = new AdHocRecognizer(
    name: 'Title Recognizer',
    supportedLanguage: 'en',
    supportedEntity: 'TITLE',
    denyList: ['Mr', 'Mr.', 'Mrs', 'Ms'],
);

$result = $presidio->anonymize(
    text: 'Mr. Smith lives at zip code 12345',
    adHocRecognizers: [$zipRecognizer, $titleRecognizer],
);
```

### Decision Process (Explainability)

Get detailed explanations of why each entity was detected (requires low-level client access):

```php
use Ecourty\PresidioClient\Model\AnalyzerRequest;

$results = $presidio->analyze(new AnalyzerRequest(
    text: 'Call me at 555-123-4567',
    returnDecisionProcess: true,
));

foreach ($results as $result) {
    $explanation = $result->getAnalysisExplanation();
    if ($explanation !== null) {
        echo $explanation->getRecognizer();              // "PhoneRecognizer"
        echo $explanation->getPatternName();             // "US Phone Regex"
        echo $explanation->getOriginalScore();           // 0.8
        echo $explanation->getScoreContextImprovement(); // 0.15
    }

    $metadata = $result->getRecognitionMetadata();       // ['recognizer_name' => '...']
}
```

### Health Checks

```php
$health = $presidio->health();

$health->isHealthy();           // true if both services are up
$health->isAnalyzerHealthy();   // true if Analyzer is reachable
$health->isAnonymizerHealthy(); // true if Anonymizer is reachable
```

### List Capabilities

```php
$presidio->getSupportedEntities(); // ['EMAIL_ADDRESS', 'PHONE_NUMBER', 'PERSON', ...]
$presidio->getRecognizers();       // [RecognizerResult, ...] (name, supported entities, languages)

$presidio->getAnonymizers();       // ['replace', 'redact', 'hash', 'mask', 'encrypt']
$presidio->getDeanonymizers();     // ['decrypt']
```

## Supported Entity Types

The `EntityType` enum provides all supported PII types:

`PERSON`, `PHONE_NUMBER`, `EMAIL_ADDRESS`, `CREDIT_CARD`, `CRYPTO`, `DATE_TIME`, `DOMAIN_NAME`, `IBAN_CODE`, `IP_ADDRESS`, `LOCATION`, `MEDICAL_LICENSE`, `NRP`, `SG_NRIC_FIN`, `UK_NHS`, `URL`, `US_BANK_NUMBER`, `US_DRIVER_LICENSE`, `US_ITIN`, `US_PASSPORT`, `US_SSN`, `AU_ABN`, `AU_ACN`, `AU_TFN`, `AU_MEDICARE`, `IN_PAN`, `IN_AADHAAR`, `IN_VEHICLE_REGISTRATION`, `IN_VOTER`, `IN_PASSPORT`

## Error Handling

API errors throw `ApiException` with the HTTP status code and response body:

```php
use Ecourty\PresidioClient\Exception\ApiException;

try {
    $analyzer->analyze($request);
} catch (ApiException $e) {
    echo $e->getStatusCode();   // 400
    echo $e->getResponseBody(); // {"error": "..."}
}
```

All exceptions extend `PresidioException` (which extends `RuntimeException`).

## Custom HTTP Client

The facade accepts pre-configured client instances. Each client accepts a Symfony `HttpClientInterface`:

```php
use Ecourty\PresidioClient\Client\PresidioAnalyzer;
use Ecourty\PresidioClient\Client\PresidioAnonymizer;
use Ecourty\PresidioClient\Presidio;
use Symfony\Component\HttpClient\HttpClient;

// Simple: custom base URLs
$presidio = new Presidio(
    analyzer: new PresidioAnalyzer('http://presidio-analyzer:5001'),
    anonymizer: new PresidioAnonymizer('http://presidio-anonymizer:5002'),
);

// Advanced: full HTTP client control
$analyzerClient = HttpClient::create(['base_uri' => 'http://presidio-analyzer:5001', 'timeout' => 10]);
$anonymizerClient = HttpClient::create(['base_uri' => 'http://presidio-anonymizer:5002', 'timeout' => 10]);

$presidio = new Presidio(
    analyzer: new PresidioAnalyzer(httpClient: $analyzerClient),
    anonymizer: new PresidioAnonymizer(httpClient: $anonymizerClient),
);

// Direct client access for advanced use cases
$analyzer = $presidio->getAnalyzer();
$anonymizer = $presidio->getAnonymizer();
```

## Examples

See the [`examples/`](examples/) directory for runnable scripts:

```bash
docker compose up -d
php examples/01-quickstart.php
```

## Development

```bash
# Start Presidio services
docker compose up -d

# Run unit tests
composer test-unit

# Run integration tests (requires Presidio services)
composer test-integration

# Run all tests
composer test

# Static analysis (PHPStan level max)
composer phpstan

# Code style check / fix
composer cs-check
composer cs-fix

# Full QA (PHPStan + CS check + tests)
composer qa
```

## License

MIT
