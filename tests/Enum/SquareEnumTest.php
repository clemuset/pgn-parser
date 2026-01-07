<?php

namespace Cmuset\PgnParser\Tests\Enum;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use PHPUnit\Framework\TestCase;

class SquareEnumTest extends TestCase
{
    public function testFileExtraction(): void
    {
        self::assertSame('a', SquareEnum::A1->file());
        self::assertSame('h', SquareEnum::H8->file());
    }

    public function testRankExtraction(): void
    {
        self::assertSame(1, SquareEnum::A1->rank());
        self::assertSame(8, SquareEnum::H8->rank());
    }

    public function testColorLightSquares(): void
    {
        self::assertSame(ColorEnum::WHITE, SquareEnum::A1->color());
        self::assertSame(ColorEnum::WHITE, SquareEnum::H8->color());
    }

    public function testColorDarkSquares(): void
    {
        self::assertSame(ColorEnum::BLACK, SquareEnum::A2->color());
        self::assertSame(ColorEnum::BLACK, SquareEnum::B1->color());
    }
}
