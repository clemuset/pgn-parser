<?php

namespace Cmuset\PgnParser\Tests\MoveApplier;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;
use PHPUnit\Framework\TestCase;

class MoveHelperTest extends TestCase
{
    private Position $position;

    protected function setUp(): void
    {
        $this->position = new Position();
        $this->position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $this->position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
    }

    public function testPathClearReturnsTrueWhenPathIsEmpty(): void
    {
        self::assertTrue(MoveHelper::isPathClear(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testPathClearReturnsFalseWhenPieceBlocksPath(): void
    {
        $this->position->setPieceAt(SquareEnum::A4, PieceEnum::WHITE_PAWN);
        self::assertFalse(MoveHelper::isPathClear(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testPathClearIgnoresSourceAndDestination(): void
    {
        $this->position->setPieceAt(SquareEnum::A1, PieceEnum::WHITE_PAWN);
        $this->position->setPieceAt(SquareEnum::A8, PieceEnum::BLACK_PAWN);
        self::assertTrue(MoveHelper::isPathClear(SquareEnum::A1, SquareEnum::A8, $this->position));
    }

    public function testSlidingMoveReturnsTrueForDiagonalMoves(): void
    {
        self::assertTrue(MoveHelper::isSlidingMove(SquareEnum::A1, SquareEnum::H8));
        self::assertTrue(MoveHelper::isSlidingMove(SquareEnum::H1, SquareEnum::A8));
    }

    public function testSlidingMoveReturnsFalseForNonDiagonalMoves(): void
    {
        self::assertFalse(MoveHelper::isSlidingMove(SquareEnum::A1, SquareEnum::A8));
        self::assertFalse(MoveHelper::isSlidingMove(SquareEnum::A1, SquareEnum::H1));
    }

    public function testSlidingMoveReturnsFalseForSameSquare(): void
    {
        self::assertFalse(MoveHelper::isSlidingMove(SquareEnum::A1, SquareEnum::A1));
    }

    public function testVerticalMoveReturnsTrueForVerticalMoves(): void
    {
        self::assertTrue(MoveHelper::isVerticalMove(SquareEnum::A1, SquareEnum::A8));
        self::assertTrue(MoveHelper::isVerticalMove(SquareEnum::H1, SquareEnum::H5));
    }

    public function testVerticalMoveReturnsFalseForNonVerticalMoves(): void
    {
        self::assertFalse(MoveHelper::isVerticalMove(SquareEnum::A1, SquareEnum::H1));
        self::assertFalse(MoveHelper::isVerticalMove(SquareEnum::A1, SquareEnum::B2));
    }

    public function testHorizontalMoveReturnsTrueForHorizontalMoves(): void
    {
        self::assertTrue(MoveHelper::isHorizontalMove(SquareEnum::A1, SquareEnum::H1));
        self::assertTrue(MoveHelper::isHorizontalMove(SquareEnum::A5, SquareEnum::D5));
    }

    public function testHorizontalMoveReturnsFalseForNonHorizontalMoves(): void
    {
        self::assertFalse(MoveHelper::isHorizontalMove(SquareEnum::A1, SquareEnum::A8));
        self::assertFalse(MoveHelper::isHorizontalMove(SquareEnum::A1, SquareEnum::B2));
    }

    public function testStraightMoveReturnsTrueForVerticalAndHorizontalMoves(): void
    {
        self::assertTrue(MoveHelper::isStraightMove(SquareEnum::A1, SquareEnum::A8));
        self::assertTrue(MoveHelper::isStraightMove(SquareEnum::A1, SquareEnum::H1));
    }

    public function testStraightMoveReturnsFalseForDiagonalMoves(): void
    {
        self::assertFalse(MoveHelper::isStraightMove(SquareEnum::A1, SquareEnum::H8));
    }

    public function testKnightMoveReturnsTrueForValidKnightMoves(): void
    {
        self::assertTrue(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::F6));
        self::assertTrue(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::D6));
        self::assertTrue(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::G5));
        self::assertTrue(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::G3));
    }

    public function testKnightMoveReturnsFalseForInvalidKnightMoves(): void
    {
        self::assertFalse(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::E5));
        self::assertFalse(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::F5));
        self::assertFalse(MoveHelper::isKnightMove(SquareEnum::E4, SquareEnum::H8));
    }

    public function testPawnMoveReturnsTrueForWhitePawnSingleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(SquareEnum::E2, SquareEnum::E3, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsTrueForWhitePawnDoubleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(SquareEnum::E2, SquareEnum::E4, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsTrueForBlackPawnSingleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(SquareEnum::E7, SquareEnum::E6, ColorEnum::BLACK));
    }

    public function testPawnMoveReturnsTrueForBlackPawnDoubleMove(): void
    {
        self::assertTrue(MoveHelper::isPawnMove(SquareEnum::E7, SquareEnum::E5, ColorEnum::BLACK));
    }

    public function testPawnMoveReturnsFalseForDoubleMoveNotFromStartingRank(): void
    {
        self::assertFalse(MoveHelper::isPawnMove(SquareEnum::E4, SquareEnum::E6, ColorEnum::WHITE));
    }

    public function testPawnMoveReturnsFalseForInvalidMoves(): void
    {
        self::assertFalse(MoveHelper::isPawnMove(SquareEnum::E2, SquareEnum::D2, ColorEnum::WHITE));
        self::assertFalse(MoveHelper::isPawnMove(SquareEnum::E2, SquareEnum::E1, ColorEnum::WHITE));
    }

    public function testPawnCaptureReturnsTrueForWhitePawnCapture(): void
    {
        self::assertTrue(MoveHelper::isPawnCaptureMove(SquareEnum::E4, SquareEnum::D5, ColorEnum::WHITE));
        self::assertTrue(MoveHelper::isPawnCaptureMove(SquareEnum::E4, SquareEnum::F5, ColorEnum::WHITE));
    }

    public function testPawnCaptureReturnsTrueForBlackPawnCapture(): void
    {
        self::assertTrue(MoveHelper::isPawnCaptureMove(SquareEnum::E5, SquareEnum::D4, ColorEnum::BLACK));
        self::assertTrue(MoveHelper::isPawnCaptureMove(SquareEnum::E5, SquareEnum::F4, ColorEnum::BLACK));
    }

    public function testPawnCaptureReturnsFalseForInvalidCaptureMoves(): void
    {
        self::assertFalse(MoveHelper::isPawnCaptureMove(SquareEnum::E4, SquareEnum::E5, ColorEnum::WHITE));
        self::assertFalse(MoveHelper::isPawnCaptureMove(SquareEnum::E4, SquareEnum::D6, ColorEnum::WHITE));
    }

    public function testKingMoveReturnsTrueForOneSquareMove(): void
    {
        self::assertTrue(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::E5));
        self::assertTrue(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::D4));
        self::assertTrue(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::D5));
    }

    public function testKingMoveReturnsFalseForMovesMoreThanOneSquare(): void
    {
        self::assertFalse(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::E6));
        self::assertFalse(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::H4));
    }

    public function testKingMoveReturnsFalseForSameSquare(): void
    {
        self::assertFalse(MoveHelper::isKingMove(SquareEnum::E4, SquareEnum::E4));
    }
}
