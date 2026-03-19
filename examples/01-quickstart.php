<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecourty\PresidioClient\Presidio;

$presidio = new Presidio();

$result = $presidio->anonymize('My name is John Smith and my email is john.smith@example.com');

echo "Anonymized text:\n";
echo $result->getText() . "\n\n";

echo "Detected entities:\n";
foreach ($result->getItems() as $item) {
    echo sprintf(
        "  - %s (operator: %s, position: %d-%d)\n",
        $item->getEntityType(),
        $item->getOperator(),
        $item->getStart(),
        $item->getEnd(),
    );
}
