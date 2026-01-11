<?php

namespace Cmuset\PgnParser\Tests\MoveApplier;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;
use PHPUnit\Framework\TestCase;

class MoveHelperTest extends TestCase
{
    private Position $position;

    protected function setUp(): void
    {
        $this->position = new Position();
        $this->position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testPathClearReturnsTrueWhenPathIsEmpty(): void
    {
        self::assertTrue(MoveHelper::isPathClear(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testPathClearReturnsFalseWhenPieceBlocksPath(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse(MoveHelper::isPathClear(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testPathClearIgnoresSourceAndDestination(): void
    {
        $this->position->setPieceAt(CoordinatesEnum::A1, PieceEnum::WHITE_PAWN);
        $this->position->setPieceAt(CoordinatesEnum::A8, PieceEnum::BLACK_PAWN);
        self::assertTrue(MoveHelper::isPathClear(CoordinatesEnum::A1, CoordinatesEnum::A8, $this->position));
    }

    public function testSlidingMoveReturnsTrueForDiagonalMoves(): void
    {
        self::assertTrue(MoveHelper::isSlidingMove(CoordinatesEnum::A1, CoordinatesEnum::H8));
        self::assertTrue(MoveHelper::isSlidingMove(CoordinatesEnum::H1, CoordinatesEnum::A8));
    }

    public function testSlidingMoveReturnsFalseForNonDiagonalMoves(): void
    {
        self::assertFalse(MoveHelper::isSlidingMove(CoordinatesEnum::A1, CoordinatesEnum::A8));
        self::assertFalse(MoveHelper::isSlidingMove(CoordinatesEnum::A1, CoordinatesEnum::H1));
    }

    public function testSlidingMoveReturnsFalseForSameSquare(): void
    {
        self::assertFalse(MoveHelper::isSlidingMove(CoordinatesEnum::A1, CoordinatesEnum::A1));
    }

    public function testVerticalMoveReturnsTrueForVerticalMoves(): void
    {
        self::assertTrue(MoveHelper::isVerticalMove(CoordinatesEnum::A1, CoordinatesEnum::A8));
        self::assertTrue(MoveHelper::isVerticalMove(CoordinatesEnum::H1, CoordinatesEnum::H5));
    }

    public function testVerticalMoveReturnsFalseForNonVerticalMoves(): void
    {
        self::assertFalse(MoveHelper::isVerticalMove(CoordinatesEnum::A1, CoordinatesEnum::H1));
        self::assertFalse(MoveHelper::isVerticalMove(CoordinatesEnum::A1, CoordinatesEnum::B2));
    }

    public function testHorizontalMoveReturnsTrueForHorizontalMoves(): void
    {
        self::assertTrue(MoveHelper::isHorizontalMove(CoordinatesEnum::A1, CoordinatesEnum::H1));
        self::assertTrue(MoveHelper::isHorizontalMove(CoordinatesEnum::A5, CoordinatesEnum::D5));
    }

    public function testHorizontalMoveReturnsFalseForNonHorizontalMoves(): void
    {
        self::assertFalse(MoveHelper::isHorizontalMove(CoordinatesEnum::A1, CoordinatesEnum::A8));
        self::assertFalse(MoveHelper::isHorizontalMove(CoordinatesEnum::A1, CoordinatesEnum::B2));
    }

    public function testStraightMoveReturnsTrueForVerticalAndHorizontalMoves(): void
    {
        self::assertTrue(MoveHelper::isStraightMove(CoordinatesEnum::A1, CoordinatesEnum::A8));
        self::assertTrue(MoveHelper::isStraightMove(CoordinatesEnum::A1, CoordinatesEnum::H1));
    }

    public function testStraightMoveReturnsFalseForDiagonalMoves(): void
    {
        self::assertFalse(MoveHelper::isStraightMove(CoordinatesEnum::A1, CoordinatesEnum::H8));
    }

    public function testKnightMoveReturnsTrueForValidKnightMoves(): void
    {
        self::assertTrue(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::F6));
        self::assertTrue(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::D6));
        self::assertTrue(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::G5));
        self::assertTrue(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::G3));
    }

    public function testKnightMoveReturnsFalseForInvalidKnightMoves(): void
    {
        self::assertFalse(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::E5));
        self::assertFalse(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::F5));
        self::assertFalse(MoveHelper::isKnightMove(CoordinatesEnum::E4, CoordinatesEnum::H8));
    }

    public function testPawnMoveReturnsTrueForWhitePawnSingleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(CoordinatesEnum::E2, CoordinatesEnum::E3, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsTrueForWhitePawnDoubleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(CoordinatesEnum::E2, CoordinatesEnum::E4, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsTrueForBlackPawnSingleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(CoordinatesEnum::E7, CoordinatesEnum::E6, ColorEnum::BLACK));
    }

    public function testPawnMoveReturnsTrueForBlackPawnDoubleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(CoordinatesEnum::E7, CoordinatesEnum::E5, ColorEnum::BLACK));
    }

    public function testPawnMoveReturnsFalseForDoubleMoveNotFromStartingRank(): void
    {
        self::assertFalse(MoveHelper::isPawnMove(CoordinatesEnum::E4, CoordinatesEnum::E6, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsFalseForInvalidMoves(): void
    {
        self::assertFalse(MoveHelper::isPawnMove(CoordinatesEnum::E2, CoordinatesEnum::D2, ColorEnum::WHITE));
        self::assertFalse(MoveHelper::isPawnMove(CoordinatesEnum::E2, CoordinatesEnum::E1, ColorEnum::WHITE));
    }

    public function testPawnCaptureReturnsTrueForWhitePawnCapture(): void
    {
        self::assertTrue(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E4, CoordinatesEnum::D5, ColorEnum::WHITE));
        self::assertTrue(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E4, CoordinatesEnum::F5, ColorEnum::WHITE));
    }

    public function testPawnCaptureReturnsTrueForBlackPawnCapture(): void
    {
        self::assertTrue(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E5, CoordinatesEnum::D4, ColorEnum::BLACK));
        self::assertTrue(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E5, CoordinatesEnum::F4, ColorEnum::BLACK));
    }

    public function testPawnCaptureReturnsFalseForInvalidCaptureMoves(): void
    {
        self::assertFalse(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E4, CoordinatesEnum::E5, ColorEnum::WHITE));
        self::assertFalse(MoveHelper::isPawnCaptureMove(CoordinatesEnum::E4, CoordinatesEnum::D6, ColorEnum::WHITE));
    }

    public function testKingMoveReturnsTrueForOneSquareMove(): void
    {
        self::assertTrue(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::E5));
        self::assertTrue(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::D4));
        self::assertTrue(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::D5));
    }

    public function testKingMoveReturnsFalseForMovesMoreThanOneSquare(): void
    {
        self::assertFalse(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::E6));
        self::assertFalse(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::H4));
    }

    public function testKingMoveReturnsFalseForSameSquare(): void
    {
        self::assertFalse(MoveHelper::isKingMove(CoordinatesEnum::E4, CoordinatesEnum::E4));
    }
}
