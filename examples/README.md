# Examples

Runnable scripts demonstrating key features of the Presidio PHP client.

## Prerequisites

1. Install dependencies:
   ```bash
   composer install
   ```

2. Start the Presidio services:
   ```bash
   docker compose up -d
   ```

## Run

```bash
php examples/01-quickstart.php
php examples/02-custom-operators.php
php examples/03-encrypt-decrypt.php
php examples/04-ad-hoc-recognizers.php
```

## What's covered

| Script | Feature |
|--------|---------|
| `01-quickstart.php` | One-call anonymize via facade |
| `02-custom-operators.php` | Replace, mask, and hash operators |
| `03-encrypt-decrypt.php` | Encrypt → decrypt round-trip |
| `04-ad-hoc-recognizers.php` | Custom pattern & deny-list recognizers |
