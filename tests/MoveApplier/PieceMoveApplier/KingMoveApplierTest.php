<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
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
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testKingAttacksOneSquareAway(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::E5, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D4, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F4, $this->position));
    }

    public function testKingAttacksDiagonallyOneSquareAway(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D5, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F3, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::F5, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::D3, $this->position));
    }

    public function testKingDoesNotAttackMoreThanOneSquareAway(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::E6, $this->position));
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::G4, $this->position));
    }

    public function testKingDoesNotAttackSameSquare(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E4, SquareEnum::E4, $this->position));
    }

    public function testKingIgnoresBlockingPieces(): void
    {
        $this->position->setPieceAt(SquareEnum::E5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(SquareEnum::E4, SquareEnum::E5, $this->position));
    }

    public function testKingAttacksFromCornerSquares(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::A2, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::B1, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::A1, SquareEnum::B2, $this->position));
    }

    public function testKingAttacksBothDirections(): void
    {
        self::assertTrue($this->applier->isAttacking(SquareEnum::E5, SquareEnum::E4, $this->position));
        self::assertTrue($this->applier->isAttacking(SquareEnum::D4, SquareEnum::E4, $this->position));
    }
}
