<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\PawnMoveApplier;
use PHPUnit\Framework\TestCase;

class PawnMoveApplierTest extends TestCase
{
    private PawnMoveApplier $applier;
    private Position $position;

    protected function setUp(): void
    {
        $this->applier = new PawnMoveApplier();
        $this->position = new Position();
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testWhitePawnCanMoveSingleSquareForward(): void
    {
        self::assertTrue($this->applier->canMove(SquareEnum::E2, SquareEnum::E3, $this->position));
    }

    public function testWhitePawnCanMoveDoubleSquareFromStartingRank(): void
    {
        self::assertTrue($this->applier->canMove(SquareEnum::E2, SquareEnum::E4, $this->position));
    }

    public function testWhitePawnCannotMoveDoubleSquareNotFromStartingRank(): void
    {
        self::assertFalse($this->applier->canMove(SquareEnum::E4, SquareEnum::E6, $this->position));
    }

    public function testWhitePawnCannotMoveBackward(): void
    {
        self::assertFalse($this->applier->canMove(SquareEnum::E4, SquareEnum::E3, $this->position));
    }

    public function testWhitePawnCannotMoveHorizontally(): void
    {
        self::assertFalse($this->applier->canMove(SquareEnum::E4, SquareEnum::F4, $this->position));
    }

    public function testBlackPawnCanMoveSingleSquareForward(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        self::assertTrue($this->applier->canMove(SquareEnum::E7, SquareEnum::E6, $this->position));
    }

    public function testBlackPawnCanMoveDoubleSquareFromStartingRank(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        self::assertTrue($this->applier->canMove(SquareEnum::E7, SquareEnum::E5, $this->position));
    }

    public function testWhitePawnCannotMoveWhenBlockedBySinglePiece(): void
    {
        $this->position->setPieceAt(SquareEnum::E3, PieceEnum::BLACK_PAWN);
        self::assertFalse($this->applier->canMove(SquareEnum::E2, SquareEnum::E4, $this->position));
    }

    public function testWhitePawnCannotMoveWhenDoublePathIsBlocked(): void
    {
        $this->position->setPieceAt(SquareEnum::E3, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->canMove(SquareEnum::E2, SquareEnum::E4, $this->position));
    }

    public function testWhitePawnCanCaptureWithTargetPiece(): void
    {
        $this->position->setPieceAt(SquareEnum::D4, PieceEnum::BLACK_PAWN);
        self::assertTrue($this->applier->isAttacking(SquareEnum::E3, SquareEnum::D4, $this->position));
    }

    public function testWhitePawnCannotCaptureWithoutTargetPiece(): void
    {
        self::assertFalse($this->applier->isAttacking(SquareEnum::E3, SquareEnum::D4, $this->position));
    }

    public function testBlackPawnCanCaptureToTheDiagonalLeft(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setPieceAt(SquareEnum::D5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(SquareEnum::E6, SquareEnum::D5, $this->position));
    }

    public function testBlackPawnCanCaptureToTheDiagonalRight(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setPieceAt(SquareEnum::F5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(SquareEnum::E6, SquareEnum::F5, $this->position));
    }

    public function testWhitePawnCanMoveOnEnPassantTarget(): void
    {
        $this->position->setSideToMove(ColorEnum::WHITE);
        $this->position->setEnPassantTarget(SquareEnum::D6);
        self::assertTrue($this->applier->canMove(SquareEnum::E5, SquareEnum::D6, $this->position));
    }

    public function testBlackPawnCanMoveOnEnPassantTarget(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setEnPassantTarget(SquareEnum::E3);
        self::assertTrue($this->applier->canMove(SquareEnum::D4, SquareEnum::E3, $this->position));
    }
}
