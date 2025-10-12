<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testInitialState(): void
    {
        $position = new Position();
        self::assertCount(64, $position->getSquares());
        self::assertSame(ColorEnum::WHITE, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(1, $position->getFullmoveNumber());
        self::assertNull($position->getEnPassantTarget());
    }

    public function testSetAndGetPieceAt(): void
    {
        $position = new Position();
        $position->setPieceAt(SquareEnum::E4, PieceEnum::WHITE_KNIGHT);
        self::assertSame(PieceEnum::WHITE_KNIGHT, $position->getPieceAt(SquareEnum::E4));
        $position->setPieceAt(SquareEnum::E4, null);
        self::assertNull($position->getPieceAt(SquareEnum::E4));
    }

    public function testSideToMoveMutation(): void
    {
        $position = new Position();
        $position->setSideToMove(ColorEnum::BLACK);
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
    }
}
