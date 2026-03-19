<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecourty\PresidioClient\Model\AdHocRecognizer;
use Ecourty\PresidioClient\Model\Pattern;
use Ecourty\PresidioClient\Presidio;

$presidio = new Presidio();

// Pattern-based: detect US zip codes
$zipRecognizer = new AdHocRecognizer(
    name: 'Zip Code Recognizer',
    supportedLanguage: 'en',
    supportedEntity: 'ZIP_CODE',
    patterns: [new Pattern('zip code (weak)', '(\b\d{5}(?:\-\d{4})?\b)', 0.01)],
    context: ['zip', 'code'],
);

// Deny-list based: detect honorific titles
$titleRecognizer = new AdHocRecognizer(
    name: 'Title Recognizer',
    supportedLanguage: 'en',
    supportedEntity: 'TITLE',
    denyList: ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.'],
);

$text = 'Dr. Smith lives at zip code 90210 and Mrs. Johnson is at 10001-1234';

$result = $presidio->anonymize(
    text: $text,
    adHocRecognizers: [$zipRecognizer, $titleRecognizer],
);

echo "Original:    {$text}\n";
echo "Anonymized:  {$result->getText()}\n\n";

echo "Detected entities:\n";
foreach ($result->getItems() as $item) {
    echo \sprintf(
        "  - %s: \"%s\" (position: %d-%d)\n",
        $item->getEntityType(),
        $item->getText(),
        $item->getStart(),
        $item->getEnd(),
    );
}
