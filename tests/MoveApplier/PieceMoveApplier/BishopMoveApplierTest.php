<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\BishopMoveApplier;
use PHPUnit\Framework\TestCase;

class BishopMoveApplierTest extends TestCase
{
    private BishopMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new BishopMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testBishopAttacksAlongDiagonalWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testBishopDoesNotAttackWhenPathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::D4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H8, $this->position));
    }

    public function testBishopDoesNotAttackVertically(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testBishopDoesNotAttackHorizontally(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::A1, SquareEnum::H1, $this->position));
    }

    public function testBishopAttacksShortDiagonal(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::D4, SquareEnum::F6, $this->position));
    }

    public function testBishopAttacksDiagonalBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::H8, SquareEnum::A1, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::H1, SquareEnum::A8, $this->position));
    }
}
