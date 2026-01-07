<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\RookMoveApplier;
use PHPUnit\Framework\TestCase;

class RookMoveApplierTest extends TestCase
{
    private RookMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new RookMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testRookAttacksVerticallyWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testRookAttacksHorizontallyWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A4, SquareEnum::H4, $this->position));
    }

    public function testRookDoesNotAttackWhenVerticalPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testRookDoesNotAttackWhenHorizontalPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::D1, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H1, $this->position));
    }

    public function testRookDoesNotAttackDiagonally(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testRookAttacksMultipleSquaresOnRank(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A4, SquareEnum::H4, $this->position));
    }

    public function testRookAttacksMultipleSquaresOnFile(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::D1, SquareEnum::D7, $this->position));
    }

    public function testRookAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::H4, SquareEnum::A4, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::A8, SquareEnum::A1, $this->position));
    }
}
