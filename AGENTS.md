# AGENTS.md - Coding Guidelines for AI Agents

## 🎯 Core Concept

**presidio-client** is a standalone PHP library providing a typed client for [Microsoft Presidio](https://github.com/microsoft/presidio) — an open-source framework for detecting and anonymizing Personally Identifiable Information (PII) in text.

### Problem Solved

When building privacy-aware applications, developers need to detect and anonymize PII (names, emails, phone numbers, etc.) in text. Microsoft Presidio provides this capability through REST APIs, but there is no official PHP client. This library fills that gap.

### Solution

A typed PHP client wrapping Presidio's two REST services:
- **PresidioAnalyzer** — Detect PII entities in text (calls Presidio Analyzer API)
- **PresidioAnonymizer** — Anonymize/deanonymize text using detected entities (calls Presidio Anonymizer API)

Both clients use Symfony HttpClient for HTTP communication and return strongly-typed immutable DTOs.

---

## 🏗️ Architecture

### Overview

```
Presidio (Facade — single entry point)
  ├── anonymize(string $text, ...): AnonymizeResult  ← convenience: analyze + anonymize in one call
  ├── health(): PresidioHealth
  ├── analyze(AnalyzerRequest): AnalyzerResult[]     ← proxy
  ├── anonymizeRequest(AnonymizeRequest): AnonymizeResult
  ├── deanonymize(DeanonymizeRequest): DeanonymizeResult
  ├── getRecognizers(): RecognizerResult[]
  ├── getSupportedEntities(): string[]
  ├── getAnonymizers(): string[]
  ├── getDeanonymizers(): string[]
  ├── getAnalyzer(): PresidioAnalyzer
  └── getAnonymizer(): PresidioAnonymizer

PresidioAnalyzer (Analyzer API client — src/Client/)
  ├── analyze(AnalyzerRequest): AnalyzerResult[]
  ├── getRecognizers(): RecognizerResult[]
  ├── getSupportedEntities(): string[]
  └── health(): bool

PresidioAnonymizer (Anonymizer API client — src/Client/)
  ├── anonymize(AnonymizeRequest): AnonymizeResult
  ├── deanonymize(DeanonymizeRequest): DeanonymizeResult
  ├── getAnonymizers(): string[]
  ├── getDeanonymizers(): string[]
  └── health(): bool
```

### Main Components

| Component            | Location                           | Role                                             |
|----------------------|------------------------------------|--------------------------------------------------|
| `Presidio`           | `src/Presidio.php`                 | Facade combining both clients                    |
| `PresidioAnalyzer`   | `src/Client/PresidioAnalyzer.php`  | HTTP client for Presidio Analyzer API            |
| `PresidioAnonymizer` | `src/Client/PresidioAnonymizer.php`| HTTP client for Presidio Anonymizer API          |
| Models (DTOs)        | `src/Model/`                       | Immutable `final readonly` request/response DTOs |
| Enums                | `src/Enum/`                        | String-backed enums for entity types & operators |
| Exceptions           | `src/Exception/`                   | Abstract base + API-specific exception           |

### Presidio API Endpoints Covered

**Analyzer** (default: `http://localhost:5001`):
- `POST /analyze` — Detect PII entities in text
- `GET /recognizers` — List available recognizers
- `GET /supportedentities` — List supported entity types
- `GET /health` — Health check

**Anonymizer** (default: `http://localhost:5002`):
- `POST /anonymize` — Anonymize text using analyzer results
- `POST /deanonymize` — Reverse anonymization
- `GET /anonymizers` — List available anonymizer operators
- `GET /deanonymizers` — List available deanonymizer operators
- `GET /health` — Health check

---

## 🚀 Typical Use Cases

- Detecting PII in user-submitted text before storage
- Anonymizing documents for GDPR/privacy compliance
- Preprocessing text before sending to LLM APIs to avoid PII leakage
- Building data pipelines that require PII redaction

---

## 💡 Design Patterns Used

- **Immutable Value Objects** — All models are `final readonly class` with getters
- **Factory Methods** — `fromArray()` static constructors for deserializing API responses
- **Fluent Interface** — Request DTOs use named constructor params

---

## Project Breakdown

### Models (`src/Model/`)

| Class                  | Purpose                                                           |
|------------------------|-------------------------------------------------------------------|
| `AnalyzerRequest`      | Request DTO for `POST /analyze` (all API params supported)        |
| `AnalyzerResult`       | Single detected PII entity (type, position, score, metadata)      |
| `AnalysisExplanation`  | Decision process explanation for a detected entity                |
| `AnonymizeRequest`     | Request DTO for `POST /anonymize`                                 |
| `AnonymizeResult`      | Response from anonymization (text + items)                        |
| `AnonymizedEntity`     | Individual anonymized entity info                                 |
| `DeanonymizeRequest`   | Request DTO for `POST /deanonymize`                               |
| `DeanonymizeResult`    | Response from deanonymization                                     |
| `OperatorConfig`       | Operator configuration (type + params)                            |
| `RecognizerResult`     | Recognizer metadata from `GET /recognizers`                       |
| `AdHocRecognizer`      | Ad-hoc recognizer definition (pattern-based or deny-list)         |
| `Pattern`              | Regex pattern for ad-hoc recognizers                              |
| `PresidioHealth`       | Health status of both Presidio services                           |

### Enums (`src/Enum/`)

| Enum              | Purpose                                          |
|-------------------|--------------------------------------------------|
| `EntityType`      | String-backed enum of PII entity types           |
| `OperatorType`    | String-backed enum of anonymization operators     |
| `AllowListMatch`  | String-backed enum for allow list matching mode   |

### Exceptions (`src/Exception/`)

| Class                | Purpose                                          |
|----------------------|--------------------------------------------------|
| `PresidioException`  | Abstract base exception (extends `\RuntimeException`) |
| `ApiException`       | HTTP/API error with status code and response body |

**IMPORTANT**: This section should evolve with the project. When a new feature is created, updated or removed, this section should too.

## 🧪 Testing

Tests are located in `tests/Unit/`. Each client and model has its own test class.

```
tests/Unit/
├── PresidioAnalyzerTest.php
├── PresidioAnonymizerTest.php
├── Model/
├── Enum/
└── Exception/
```

Run tests: `composer test`

Docker Compose is provided for running Presidio services locally:
```bash
docker compose up -d
```

---

## Remarks & Guidelines

### General

- NEVER commit or push the git repository.
- When unsure about something, you MUST ask the user for clarification.
- Always choose robust solutions over hacky fixes.
- ALWAYS write tests for new components.
- Do NOT write type documentation unless explicitly asked.
- Once a feature is complete, update `README.md` and `AGENTS.md` accordingly.
- **No magic strings/values**: use named constants (`const`) for default values, API endpoints, HTTP status codes, and any repeated literal. Examples: `DEFAULT_BASE_URL`, `ENDPOINT_ANALYZE`, `DEFAULT_LANGUAGE`, `HTTP_OK`.

### Adding a new Model

1. Create `src/Model/MyModel.php` as a `final readonly class`
2. Use named constructor parameters (promoted properties with getters)
3. Add a `fromArray(array $data): self` static factory for API deserialization
4. Add a `toArray(): array` method for request DTOs that need serialization
5. Add unit tests in `tests/Unit/Model/`

### Adding a new Enum

1. Create `src/Enum/MyEnum.php` as a `string`-backed enum
2. Add unit tests in `tests/Unit/Enum/`

## 📚 References

- **Source code**: `/src`
- **Tests**: `/tests`
- **README**: User documentation
- **Microsoft Presidio**: https://github.com/microsoft/presidio
- **Presidio API docs**: https://microsoft.github.io/presidio/
