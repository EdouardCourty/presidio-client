<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecourty\PresidioClient\Enum\OperatorType;
use Ecourty\PresidioClient\Model\OperatorConfig;
use Ecourty\PresidioClient\Presidio;

$presidio = new Presidio();

$text = 'Contact Jane Doe at jane.doe@acme.com or call 555-867-5309';

// Replace names, mask emails, hash phone numbers
$result = $presidio->anonymize(
    text: $text,
    operators: [
        'PERSON' => new OperatorConfig(OperatorType::REPLACE, ['new_value' => '[REDACTED]']),
        'EMAIL_ADDRESS' => new OperatorConfig(OperatorType::MASK, [
            'masking_char' => '*',
            'chars_to_mask' => 20,
            'from_end' => false,
        ]),
        'PHONE_NUMBER' => new OperatorConfig(OperatorType::HASH, ['hash_type' => 'sha256']),
    ],
);

echo "Original:    {$text}\n";
echo "Anonymized:  {$result->getText()}\n\n";

echo "Applied operators:\n";
foreach ($result->getItems() as $item) {
    echo sprintf(
        "  - %s → %s (operator: %s)\n",
        $item->getEntityType(),
        $item->getText(),
        $item->getOperator(),
    );
}
