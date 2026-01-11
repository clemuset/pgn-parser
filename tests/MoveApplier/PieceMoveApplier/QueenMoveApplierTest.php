<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\QueenMoveApplier;
use PHPUnit\Framework\TestCase;

class QueenMoveApplierTest extends TestCase
{
    private QueenMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new QueenMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testQueenAttacksVertically(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A4, CoordinatesEnum::A8, $this->position));
    }

    public function testQueenAttacksHorizontally(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A4, CoordinatesEnum::H4, $this->position));
    }

    public function testQueenAttacksDiagonally(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testQueenDoesNotAttackWhenVerticalPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testQueenDoesNotAttackWhenHorizontalPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H1, $this->position));
    }

    public function testQueenDoesNotAttackWhenDiagonalPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::D4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testQueenDoesNotAttackKnightMoves(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F6, $this->position));
    }

    public function testQueenCombinesRookAndBishopMoves(): void
    {
        // Rook-like move (vertical)
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E1, CoordinatesEnum::E7, $this->position));
        // Bishop-like move (diagonal)
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::D1, CoordinatesEnum::H5, $this->position));
    }

    public function testQueenAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::H8, CoordinatesEnum::A1, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }
}
