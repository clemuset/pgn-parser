<?php

namespace Cmuset\PgnParser\Tests\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveApplier;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class MoveApplierTest extends TestCase
{
    private MoveApplier $applier;

    protected function setUp(): void
    {
        $this->applier = new MoveApplier();
    }

    public function testApplySimplePawnMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        self::assertSame(PieceEnum::WHITE_PAWN, $newPosition->getPieceAt(SquareEnum::E4));
        self::assertNull($newPosition->getPieceAt(SquareEnum::E2));
        self::assertSame(ColorEnum::BLACK, $newPosition->getSideToMove());
    }

    public function testApplyMoveMaintainsCastlingRights(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        // White castling rights should remain after white's move
        self::assertTrue($newPosition->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));

        $move = Move::fromSAN('e5', ColorEnum::BLACK);
        $newPosition = $this->applier->apply($newPosition, $move);

        // Still should have castling rights before moving king/rook
        self::assertTrue($newPosition->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
    }

    public function testApplyMoveRemovesCastlingRightsWhenKingMoves(): void
    {
        $position = Position::fromFEN('r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1');
        $move = Move::fromSAN('Ke2', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        self::assertFalse($newPosition->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
        self::assertFalse($newPosition->hasCastlingRight(CastlingEnum::WHITE_QUEENSIDE));
        self::assertTrue($newPosition->hasCastlingRight(CastlingEnum::BLACK_KINGSIDE));
    }

    public function testApplyMoveRemovesCastlingRightsWhenRookMovesFromQueenside(): void
    {
        $position = Position::fromFEN('r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1');
        $move = Move::fromSAN('Ra2', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        self::assertFalse($newPosition->hasCastlingRight(CastlingEnum::WHITE_QUEENSIDE));
        self::assertTrue($newPosition->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
    }

    public function testApplyMoveIncrementsHalfmoveClockForNonCaptureMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        self::assertSame(0, $newPosition->getHalfmoveClock());
    }

    public function testApplyMoveResetsHalfmoveClockForPawnMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1');
        $position->setHalfmoveClock(5);
        $move = Move::fromSAN('e5', ColorEnum::BLACK);

        $newPosition = $this->applier->apply($position, $move);

        self::assertSame(0, $newPosition->getHalfmoveClock());
    }

    public function testApplyMoveSwitchesColor(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $newPosition = $this->applier->apply($position, $move);

        self::assertSame(ColorEnum::BLACK, $newPosition->getSideToMove());
    }

    public function testApplyMoveIncrementsFullmoveNumberAfterBlackMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);
        $newPosition = $this->applier->apply($position, $move);

        self::assertSame(1, $newPosition->getFullmoveNumber());

        $move = Move::fromSAN('e5', ColorEnum::BLACK);
        $newPosition = $this->applier->apply($newPosition, $move);

        self::assertSame(2, $newPosition->getFullmoveNumber());
    }

    public function testApplyMoveThrowsExceptionForWrongColorToMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e5', ColorEnum::BLACK);

        self::expectException(MoveApplyingException::class);
        $this->applier->apply($position, $move);
    }

    public function testApplyMoveThrowsExceptionWhenMovingNonExistentPiece(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $move = Move::fromSAN('Qh5', ColorEnum::WHITE);

        self::expectException(MoveApplyingException::class);
        $this->applier->apply($position, $move);
    }

    public function testApplyMoveThrowsExceptionForMoveMarkedAsCheckButIsNot(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);
        $move->setIsCheck(true);

        self::expectException(MoveApplyingException::class);
        $this->applier->apply($position, $move);
    }

    public function testApplyMoveThrowsExceptionForMoveMarkedAsCheckmateButIsNot(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);
        $move->setIsCheckmate(true);

        self::expectException(MoveApplyingException::class);
        $this->applier->apply($position, $move);
    }

    public function testEnPassantTargetChange(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);
        $newPosition = $this->applier->apply($position, $move);
        self::assertSame(SquareEnum::E3, $newPosition->getEnPassantTarget());

        $move = Move::fromSAN('d5', ColorEnum::BLACK);
        $newPosition = $this->applier->apply($newPosition, $move);
        self::assertSame(SquareEnum::D6, $newPosition->getEnPassantTarget());

        $move = Move::fromSAN('Nf3', ColorEnum::WHITE);
        $newPosition = $this->applier->apply($newPosition, $move);
        self::assertNull($newPosition->getEnPassantTarget());
    }
}
