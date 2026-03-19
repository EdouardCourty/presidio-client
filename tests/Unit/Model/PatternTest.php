<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Tests\Unit\Model;

use Ecourty\PresidioClient\Model\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
class PatternTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $pattern = new Pattern(
            name: 'zip code (weak)',
            regex: '(\b\d{5}(?:\-\d{4})?\b)',
            score: 0.01,
        );

        self::assertSame('zip code (weak)', $pattern->getName());
        self::assertSame('(\b\d{5}(?:\-\d{4})?\b)', $pattern->getRegex());
        self::assertSame(0.01, $pattern->getScore());
    }

    public function testToArray(): void
    {
        $pattern = new Pattern(
            name: 'phone pattern',
            regex: '\d{3}-\d{3}-\d{4}',
            score: 0.75,
        );

        self::assertSame([
            'name' => 'phone pattern',
            'regex' => '\d{3}-\d{3}-\d{4}',
            'score' => 0.75,
        ], $pattern->toArray());
    }

    public function testFromArray(): void
    {
        $pattern = Pattern::fromArray([
            'name' => 'email pattern',
            'regex' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+',
            'score' => 0.9,
        ]);

        self::assertSame('email pattern', $pattern->getName());
        self::assertSame('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+', $pattern->getRegex());
        self::assertSame(0.9, $pattern->getScore());
    }

    public function testRoundTrip(): void
    {
        $data = [
            'name' => 'test',
            'regex' => '\d+',
            'score' => 0.5,
        ];

        self::assertSame($data, Pattern::fromArray($data)->toArray());
    }
}
