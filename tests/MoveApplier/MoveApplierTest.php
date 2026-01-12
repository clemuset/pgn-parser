<?php

namespace Cmuset\PgnParser\Tests\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\Exception\MoveApplyingException;
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

        $this->applier->apply($position, $move);

        self::assertSame(PieceEnum::WHITE_PAWN, $position->getPieceAt(CoordinatesEnum::E4));
        self::assertNull($position->getPieceAt(CoordinatesEnum::E2));
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
    }

    public function testApplyMoveMaintainsCastlingRights(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $this->applier->apply($position, $move);

        // White castling rights should remain after white's move
        self::assertTrue($position->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));

        $move = Move::fromSAN('e5', ColorEnum::BLACK);
        $this->applier->apply($position, $move);

        // Still should have castling rights before moving king/rook
        self::assertTrue($position->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
    }

    public function testApplyMoveRemovesCastlingRightsWhenKingMoves(): void
    {
        $position = Position::fromFEN('r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1');
        $move = Move::fromSAN('Ke2', ColorEnum::WHITE);

        $this->applier->apply($position, $move);

        self::assertFalse($position->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
        self::assertFalse($position->hasCastlingRight(CastlingEnum::WHITE_QUEENSIDE));
        self::assertTrue($position->hasCastlingRight(CastlingEnum::BLACK_KINGSIDE));
    }

    public function testApplyMoveRemovesCastlingRightsWhenRookMovesFromQueenside(): void
    {
        $position = Position::fromFEN('r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1');
        $move = Move::fromSAN('Ra2', ColorEnum::WHITE);

        $this->applier->apply($position, $move);

        self::assertFalse($position->hasCastlingRight(CastlingEnum::WHITE_QUEENSIDE));
        self::assertTrue($position->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE));
    }

    public function testApplyMoveIncrementsHalfmoveClockForNonCaptureMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $this->applier->apply($position, $move);

        self::assertSame(0, $position->getHalfmoveClock());
    }

    public function testApplyMoveResetsHalfmoveClockForPawnMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1');
        $position->setHalfmoveClock(5);
        $move = Move::fromSAN('e5', ColorEnum::BLACK);

        $this->applier->apply($position, $move);

        self::assertSame(0, $position->getHalfmoveClock());
    }

    public function testApplyMoveSwitchesColor(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);

        $this->applier->apply($position, $move);

        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
    }

    public function testApplyMoveIncrementsFullmoveNumberAfterBlackMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $move = Move::fromSAN('e4', ColorEnum::WHITE);
        $this->applier->apply($position, $move);

        self::assertSame(1, $position->getFullmoveNumber());

        $move = Move::fromSAN('e5', ColorEnum::BLACK);
        $this->applier->apply($position, $move);

        self::assertSame(2, $position->getFullmoveNumber());
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
        $this->applier->apply($position, $move);
        self::assertSame(CoordinatesEnum::E3, $position->getEnPassantTarget());

        $move = Move::fromSAN('d5', ColorEnum::BLACK);
        $this->applier->apply($position, $move);
        self::assertSame(CoordinatesEnum::D6, $position->getEnPassantTarget());

        $move = Move::fromSAN('Nf3', ColorEnum::WHITE);
        $this->applier->apply($position, $move);
        self::assertNull($position->getEnPassantTarget());
    }

    public function testCanCastling(): void
    {
        $position = Position::fromFEN('r3k2r/8/8/8/8/8/8/R3K2R w KQkq - 0 1');
        $move = Move::fromSAN('O-O', ColorEnum::WHITE);
        $this->applier->apply($position, $move);
        self::assertSame(PieceEnum::WHITE_KING, $position->getPieceAt(CoordinatesEnum::G1));
        self::assertSame(PieceEnum::WHITE_ROOK, $position->getPieceAt(CoordinatesEnum::F1));
        self::assertNull($position->getPieceAt(CoordinatesEnum::E1));
        self::assertNull($position->getPieceAt(CoordinatesEnum::H1));
    }
}
