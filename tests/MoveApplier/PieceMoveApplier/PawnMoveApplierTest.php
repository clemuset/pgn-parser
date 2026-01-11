<?php

namespace Cmuset\PgnParser\Tests\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
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
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testWhitePawnCanMoveSingleSquareForward(): void
    {
        self::assertTrue($this->applier->canMove(CoordinatesEnum::E2, CoordinatesEnum::E3, $this->position));
    }

    public function testWhitePawnCanMoveDoubleSquareFromStartingRank(): void
    {
        self::assertTrue($this->applier->canMove(CoordinatesEnum::E2, CoordinatesEnum::E4, $this->position));
    }

    public function testWhitePawnCannotMoveDoubleSquareNotFromStartingRank(): void
    {
        self::assertFalse($this->applier->canMove(CoordinatesEnum::E4, CoordinatesEnum::E6, $this->position));
    }

    public function testWhitePawnCannotMoveBackward(): void
    {
        self::assertFalse($this->applier->canMove(CoordinatesEnum::E4, CoordinatesEnum::E3, $this->position));
    }

    public function testWhitePawnCannotMoveHorizontally(): void
    {
        self::assertFalse($this->applier->canMove(CoordinatesEnum::E4, CoordinatesEnum::F4, $this->position));
    }

    public function testBlackPawnCanMoveSingleSquareForward(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        self::assertTrue($this->applier->canMove(CoordinatesEnum::E7, CoordinatesEnum::E6, $this->position));
    }

    public function testBlackPawnCanMoveDoubleSquareFromStartingRank(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        self::assertTrue($this->applier->canMove(CoordinatesEnum::E7, CoordinatesEnum::E5, $this->position));
    }

    public function testWhitePawnCannotMoveWhenBlockedBySinglePiece(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::E3, PieceEnum::BLACK_PAWN);
        self::assertFalse($this->applier->canMove(CoordinatesEnum::E2, CoordinatesEnum::E4, $this->position));
    }

    public function testWhitePawnCannotMoveWhenDoublePathIsBlocked(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::E3, PieceEnum::WHITE_PAWN);
        self::assertFalse($this->applier->canMove(CoordinatesEnum::E2, CoordinatesEnum::E4, $this->position));
    }

    public function testWhitePawnCanCaptureWithTargetPiece(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::D4, PieceEnum::BLACK_PAWN);
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E3, CoordinatesEnum::D4, $this->position));
    }

    public function testWhitePawnCannotCaptureWithoutTargetPiece(): void
    {
        self::assertFalse($this->applier->isAttacking(CoordinatesEnum::E3, CoordinatesEnum::D4, $this->position));
    }

    public function testBlackPawnCanCaptureToTheDiagonalLeft(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setPieceAt(CoordinatesEnum::D5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E6, CoordinatesEnum::D5, $this->position));
    }

    public function testBlackPawnCanCaptureToTheDiagonalRight(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setPieceAt(CoordinatesEnum::F5, PieceEnum::WHITE_PAWN);
        self::assertTrue($this->applier->isAttacking(CoordinatesEnum::E6, CoordinatesEnum::F5, $this->position));
    }

    public function testWhitePawnCanMoveOnEnPassantTarget(): void
    {
        $this->position->setSideToMove(ColorEnum::WHITE);
        $this->position->setEnPassantTarget(CoordinatesEnum::D6);
        self::assertTrue($this->applier->canMove(CoordinatesEnum::E5, CoordinatesEnum::D6, $this->position));
    }

    public function testBlackPawnCanMoveOnEnPassantTarget(): void
    {
        $this->position->setSideToMove(ColorEnum::BLACK);
        $this->position->setEnPassantTarget(CoordinatesEnum::E3);
        self::assertTrue($this->applier->canMove(CoordinatesEnum::D4, CoordinatesEnum::E3, $this->position));
    }
}
