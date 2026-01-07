<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
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
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testQueenAttacksVertically(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A4, SquareEnum::A8, $this->position));
    }

    public function testQueenAttacksHorizontally(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A4, SquareEnum::H4, $this->position));
    }

    public function testQueenAttacksDiagonally(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testQueenDoesNotAttackWhenVerticalPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testQueenDoesNotAttackWhenHorizontalPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::D1, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H1, $this->position));
    }

    public function testQueenDoesNotAttackWhenDiagonalPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::D4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testQueenDoesNotAttackKnightMoves(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F6, $this->position));
    }

    public function testQueenCombinesRookAndBishopMoves(): void
    {
        // Rook-like move (vertical)
        self::assertTrue($this->applier->isAttacking(SquareEnum::E1, SquareEnum::E7, $this->position));
        // Bishop-like move (diagonal)
        self::assertTrue($this->applier->isAttacking(SquareEnum::D1, SquareEnum::H5, $this->position));
    }

    public function testQueenAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::H8, SquareEnum::A1, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }
}
