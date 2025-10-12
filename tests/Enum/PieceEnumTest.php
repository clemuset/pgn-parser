<?php

namespace Cmuset\PgnParser\Tests\Enum;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use PHPUnit\Framework\TestCase;

class PieceEnumTest extends TestCase
{
    public function testColorDetectionWhite(): void
    {
        self::assertSame(ColorEnum::WHITE, PieceEnum::WHITE_QUEEN->color());
    }

    public function testColorDetectionBlack(): void
    {
        self::assertSame(ColorEnum::BLACK, PieceEnum::BLACK_KNIGHT->color());
    }

    public function testIsPawnTrue(): void
    {
        self::assertTrue(PieceEnum::WHITE_PAWN->isPawn());
    }

    public function testIsPawnFalse(): void
    {
        self::assertFalse(PieceEnum::WHITE_KING->isPawn());
    }

    public function testFactoryDefaultsToWhiteWhenColorNull(): void
    {
        self::assertSame(PieceEnum::WHITE_KING, PieceEnum::king());
        self::assertSame(PieceEnum::WHITE_QUEEN, PieceEnum::queen());
        self::assertSame(PieceEnum::WHITE_ROOK, PieceEnum::rook());
        self::assertSame(PieceEnum::WHITE_BISHOP, PieceEnum::bishop());
        self::assertSame(PieceEnum::WHITE_KNIGHT, PieceEnum::knight());
        self::assertSame(PieceEnum::WHITE_PAWN, PieceEnum::pawn());
    }

    public function testFactoryWithBlackColor(): void
    {
        self::assertSame(PieceEnum::BLACK_KING, PieceEnum::king(ColorEnum::BLACK));
    }
}
