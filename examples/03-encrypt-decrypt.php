<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\DeanonymizeRequest;
use Ecourty\PresidioClient\Model\OperatorConfig;
use Ecourty\PresidioClient\Presidio;

$presidio = new Presidio();

$text = 'My name is Alice Johnson and my phone is 212-555-1234';
$key = 'WmZq4t7w!z%C&F)J'; // 128-bit AES key

// Step 1: Encrypt all PII
$anonymized = $presidio->anonymize(
    text: $text,
    operators: [
        'DEFAULT' => new OperatorConfig(OperatorType::ENCRYPT, ['key' => $key]),
    ],
);

echo "Original:    {$text}\n";
echo "Encrypted:   {$anonymized->getText()}\n\n";

// Step 2: Decrypt back to original
$deanonymized = $presidio->deanonymize(new DeanonymizeRequest(
    text: $anonymized->getText(),
    anonymizerResults: $anonymized->getItems(),
    deanonymizers: [
        'DEFAULT' => new OperatorConfig(OperatorType::DECRYPT, ['key' => $key]),
    ],
));

echo "Decrypted:   {$deanonymized->getText()}\n";
echo 'Round-trip OK: ' . ($deanonymized->getText() === $text ? 'yes' : 'no') . "\n";
