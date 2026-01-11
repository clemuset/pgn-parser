<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    public function testDefaultState(): void
    {
        $move = new Move();
        self::assertNull($move->getSquareFrom());
        self::assertNull($move->getTo());
        self::assertNull($move->getPromotion());
        self::assertFalse($move->isCapture());
        self::assertFalse($move->isCheck());
        self::assertFalse($move->isCheckmate());
        self::assertFalse($move->isCastling());
    }

    public function testSetters(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_BISHOP);
        $move->setSquareFrom(CoordinatesEnum::C1);
        $move->setTo(CoordinatesEnum::H6);
        $move->setPromotion(PieceEnum::WHITE_QUEEN);
        $move->setIsCapture(true);
        $move->setIsCheck(true);
        $move->setIsCheckmate(false);
        $move->setCastling(CastlingEnum::WHITE_KINGSIDE);
        $move->setAnnotation('!');

        self::assertSame(PieceEnum::WHITE_BISHOP, $move->getPiece());
        self::assertSame(CoordinatesEnum::C1, $move->getSquareFrom());
        self::assertSame(CoordinatesEnum::H6, $move->getTo());
        self::assertSame(PieceEnum::WHITE_QUEEN, $move->getPromotion());
        self::assertTrue($move->isCapture());
        self::assertTrue($move->isCheck());
        self::assertFalse($move->isCheckmate());
        self::assertTrue($move->isCastling());
        self::assertSame('!', $move->getAnnotation());
    }

    public function testCastlingFlag(): void
    {
        $move = new Move();
        self::assertFalse($move->isCastling());
        $move->setCastling(CastlingEnum::WHITE_QUEENSIDE);
        self::assertTrue($move->isCastling());
    }
}
