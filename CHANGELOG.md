# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-03-19

### Added

- `Presidio` facade — single entry point combining Analyzer and Anonymizer clients
- `PresidioAnalyzer` client — analyze text for PII entities (`POST /analyze`, `GET /recognizers`, `GET /supportedentities`, `GET /health`)
- `PresidioAnonymizer` client — anonymize and deanonymize text (`POST /anonymize`, `POST /deanonymize`, `GET /anonymizers`, `GET /deanonymizers`, `GET /health`)
- Convenience `Presidio::anonymize()` — analyze + anonymize in a single call
- All Presidio API parameters supported: entity filtering, score threshold, context words, allow lists, ad-hoc recognizers, decision process
- Immutable DTOs (`final readonly class`) for all request/response models
- `EntityType` enum — 29 PII entity types (PERSON, EMAIL_ADDRESS, CREDIT_CARD, US_SSN, IN_AADHAAR, etc.)
- `OperatorType` enum — 8 anonymization operators (replace, redact, hash, mask, encrypt, decrypt, custom, keep)
- `AllowListMatch` enum — exact and regex matching modes
- Encrypt/decrypt round-trip support for reversible anonymization
- Ad-hoc recognizer support (pattern-based and deny-list-based)
- `PresidioHealth` model for combined service health checks
- `ApiException` with HTTP status code and response body
- Resilient health checks — returns `false` on transport errors instead of throwing
- Symfony HttpClient integration with full custom HTTP client support
- Docker Compose configuration for local Presidio services
- Unit tests (PHPUnit 12) and integration tests
- PHPStan analysis at maximum level
- PHP-CS-Fixer with PSR-12 + Symfony rules
- CI workflow (GitHub Actions) for PHP 8.3, 8.4, 8.5
