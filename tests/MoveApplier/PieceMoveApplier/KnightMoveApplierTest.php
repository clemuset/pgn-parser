<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
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
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testKnightAttacksFromCentralPosition(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F6, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D6, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::G5, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::G3, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F2, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D2, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::C3, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::C5, $this->position));
    }

    public function testKnightDoesNotAttackAdjacentSquares(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::E5, $this->position));
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D4, $this->position));
    }

    public function testKnightDoesNotAttackDiagonallyAdjacentSquares(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D5, $this->position));
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F5, $this->position));
    }

    public function testKnightDoesNotAttackBishopDiagonals(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::H8, $this->position));
    }

    public function testKnightIgnoresBlockingPieces(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::F5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F6, $this->position));
    }

    public function testKnightAttacksFromCorner(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::B3, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::C2, $this->position));
    }

    public function testKnightAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::F6, CoordinatesEnum::E4, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::D6, CoordinatesEnum::E4, $this->position));
    }
}
