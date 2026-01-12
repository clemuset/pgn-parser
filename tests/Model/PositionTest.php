<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testInitialState(): void
    {
        $position = new Position();
        self::assertCount(64, $position->getSquares());
        self::assertSame(ColorEnum::WHITE, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(1, $position->getFullmoveNumber());
        self::assertNull($position->getEnPassantTarget());
    }

    public function testSetAndGetPieceAt(): void
    {
        $position = new Position();
        $position->setPieceAt(CoordinatesEnum::E4, PieceEnum::WHITE_KNIGHT);
        self::assertSame(PieceEnum::WHITE_KNIGHT, $position->getPieceAt(CoordinatesEnum::E4));
        $position->setPieceAt(CoordinatesEnum::E4, null);
        self::assertNull($position->getPieceAt(CoordinatesEnum::E4));
    }

    public function testSideToMoveMutation(): void
    {
        $position = new Position();
        $position->setSideToMove(ColorEnum::BLACK);
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
    }

    public function testGetLegalMovesFromInitialPosition(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $moves = $position->getLegalMoves();
        self::assertCount(20, $moves, 'Initial position should have 20 legal moves for white');

        // ensure only white moves are proposed and no illegal moves like moving a blocked piece backward
        foreach ($moves as $move) {
            self::assertSame(ColorEnum::WHITE, $move->getPiece()->color());
        }
    }

    public function testGetLegalMovesAfterSimpleMove(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $position->applyMove(Move::fromSAN('e4', ColorEnum::WHITE));
        $moves = $position->getLegalMoves();
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
        self::assertCount(20, $moves, 'After e4, black should still have 20 legal moves');
    }

    public function testGetLegalMovesInStalematePositionIsZero(): void
    {
        // Classic stalemate: Black to move, no legal moves, not in check
        $fen = '7k/5Q2/6K1/8/8/8/8/8 b - - 0 1';
        $position = Position::fromFEN($fen);
        self::assertTrue($position->isStaleMate());
        $moves = $position->getLegalMoves();
        self::assertCount(0, $moves, 'Stalemate position must have 0 legal moves');
    }

    public function testGetLegalMovesInCheckmatePositionIsZero(): void
    {
        // Simple checkmate: Black king boxed in by white queen and king
        $fen = 'k7/1QK5/8/8/8/8/8/8 b - - 0 1';
        $position = Position::fromFEN($fen);
        self::assertTrue($position->isCheckmate());
        $moves = $position->getLegalMoves();
        self::assertCount(0, $moves, 'Checkmate position must have 0 legal moves');
    }

    public function testIllegalMovesAreNotIncluded(): void
    {
        // Position where moving a pinned piece would expose the king; ensure such moves are not listed
        $fen = '4k3/8/8/8/8/8/4R3/4K3 w - - 0 1'; // White rook on e2, black king e8, white king e1
        $position = Position::fromFEN($fen);
        $moves = $position->getLegalMoves();

        // Build SAN set to quickly check that Re1-e2 pinned illegal move (e2->e1 occupies own king square) is not present
        $sanSet = array_map(fn (Move $m) => $m->getSAN(), $moves);
        self::assertNotContains('Re1', $sanSet);
    }

    public function testApplyingMoveUpdatesPositionCorrectly(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $position->applyMove('e4');

        self::assertNull($position->getPieceAt(CoordinatesEnum::E2));
        self::assertSame(PieceEnum::WHITE_PAWN, $position->getPieceAt(CoordinatesEnum::E4));
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(1, $position->getFullmoveNumber());

        $position->applyMove('e5');
        self::assertSame(PieceEnum::BLACK_PAWN, $position->getPieceAt(CoordinatesEnum::E5));
        self::assertSame(ColorEnum::WHITE, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(2, $position->getFullmoveNumber());

        $position->applyMove('Nf3');
        self::assertSame(PieceEnum::WHITE_KNIGHT, $position->getPieceAt(CoordinatesEnum::F3));
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(2, $position->getFullmoveNumber());
    }
}
