<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\KnightMoveApplier;
use PHPUnit\Framework\TestCase;

class KnightMoveApplierTest extends TestCase
{
    private KnightMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new KnightMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testKnightAttacksFromCentralPosition(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F6, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D6, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::G5, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::G3, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F2, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D2, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::C3, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::C5, $this->position));
    }

    public function testKnightDoesNotAttackAdjacentSquares(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::E5, $this->position));
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D4, $this->position));
    }

    public function testKnightDoesNotAttackDiagonallyAdjacentSquares(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D5, $this->position));
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F5, $this->position));
    }

    public function testKnightDoesNotAttackBishopDiagonals(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testKnightIgnoresBlockingPieces(): void
    {
        $this->position->setPieceAt(SquareEnum::F5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F6, $this->position));
    }

    public function testKnightAttacksFromCorner(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::B3, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::C2, $this->position));
    }

    public function testKnightAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::F6, SquareEnum::E4, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::D6, SquareEnum::E4, $this->position));
    }
}
