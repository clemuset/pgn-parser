<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
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
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testBishopAttacksAlongDiagonalWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testBishopDoesNotAttackWhenPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::D4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testBishopDoesNotAttackVertically(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testBishopDoesNotAttackHorizontally(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H1, $this->position));
    }

    public function testBishopAttacksShortDiagonal(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::D4, CoordinatesEnum::F6, $this->position));
    }

    public function testBishopAttacksDiagonalBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::H8, CoordinatesEnum::A1, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::H1, CoordinatesEnum::A8, $this->position));
    }
}
