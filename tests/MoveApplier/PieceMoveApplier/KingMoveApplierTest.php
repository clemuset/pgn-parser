<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\KingMoveApplier;
use PHPUnit\Framework\TestCase;

class KingMoveApplierTest extends TestCase
{
    private KingMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new KingMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testKingAttacksOneSquareAway(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::E5, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D4, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F4, $this->position));
    }

    public function testKingAttacksDiagonallyOneSquareAway(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D5, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F3, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::F5, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::D3, $this->position));
    }

    public function testKingDoesNotAttackMoreThanOneSquareAway(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::E6, $this->position));
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::G4, $this->position));
    }

    public function testKingDoesNotAttackSameSquare(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::E4, $this->position));
    }

    public function testKingIgnoresBlockingPieces(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::E5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E4, CoordinatesEnum::E5, $this->position));
    }

    public function testKingAttacksFromCornerSquares(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::A2, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::B1, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::A1, CoordinatesEnum::B2, $this->position));
    }

    public function testKingAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E5, CoordinatesEnum::E4, $this->position));
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::D4, CoordinatesEnum::E4, $this->position));
    }
}
