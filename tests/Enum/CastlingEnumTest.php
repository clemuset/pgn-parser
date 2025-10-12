<?php

namespace Cmuset\PgnParser\Tests\Enum;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use PHPUnit\Framework\TestCase;

class CastlingEnumTest extends TestCase
{
    public function testKingsideCastlingWhite(): void
    {
        self::assertSame(CastlingEnum::WHITE_KINGSIDE, CastlingEnum::kingside(ColorEnum::WHITE));
    }

    public function testKingsideCastlingBlack(): void
    {
        self::assertSame(CastlingEnum::BLACK_KINGSIDE, CastlingEnum::kingside(ColorEnum::BLACK));
    }

    public function testQueensideCastlingWhite(): void
    {
        self::assertSame(CastlingEnum::WHITE_QUEENSIDE, CastlingEnum::queenside(ColorEnum::WHITE));
    }

    public function testQueensideCastlingBlack(): void
    {
        self::assertSame(CastlingEnum::BLACK_QUEENSIDE, CastlingEnum::queenside(ColorEnum::BLACK));
    }
}
