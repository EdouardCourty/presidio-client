<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Model\PresidioHealth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PresidioHealth::class)]
class PresidioHealthTest extends TestCase
{
    public function testBothHealthy(): void
    {
        $health = new PresidioHealth(analyzer: true, anonymizer: true);

        self::assertTrue($health->isAnalyzerHealthy());
        self::assertTrue($health->isAnonymizerHealthy());
        self::assertTrue($health->isHealthy());
    }

    public function testAnalyzerDown(): void
    {
        $health = new PresidioHealth(analyzer: false, anonymizer: true);

        self::assertFalse($health->isAnalyzerHealthy());
        self::assertTrue($health->isAnonymizerHealthy());
        self::assertFalse($health->isHealthy());
    }

    public function testAnonymizerDown(): void
    {
        $health = new PresidioHealth(analyzer: true, anonymizer: false);

        self::assertTrue($health->isAnalyzerHealthy());
        self::assertFalse($health->isAnonymizerHealthy());
        self::assertFalse($health->isHealthy());
    }

    public function testBothDown(): void
    {
        $health = new PresidioHealth(analyzer: false, anonymizer: false);

        self::assertFalse($health->isHealthy());
    }

    public function testToArray(): void
    {
        $health = new PresidioHealth(analyzer: true, anonymizer: false);

        self::assertSame([
            'analyzer' => true,
            'anonymizer' => false,
        ], $health->toArray());
    }

    public function testFromArray(): void
    {
        $health = PresidioHealth::fromArray([
            'analyzer' => false,
            'anonymizer' => true,
        ]);

        self::assertFalse($health->isAnalyzerHealthy());
        self::assertTrue($health->isAnonymizerHealthy());
    }
}
