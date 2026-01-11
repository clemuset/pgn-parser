<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
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
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testRookAttacksVerticallyWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testRookAttacksHorizontallyWhenPathIsClear(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A4, CoordinatesEnum::H4, $this->position));
    }

    public function testRookDoesNotAttackWhenVerticalPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testRookDoesNotAttackWhenHorizontalPathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H1, $this->position));
    }

    public function testRookDoesNotAttackDiagonally(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testRookAttacksMultipleSquaresOnRank(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A4, CoordinatesEnum::H4, $this->position));
    }

    public function testRookAttacksMultipleSquaresOnFile(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::D1, CoordinatesEnum::D7, $this->position));
    }

    public function testRookAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::H4, CoordinatesEnum::A4, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A8, CoordinatesEnum::A1, $this->position));
    }
}
