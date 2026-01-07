<?php

namespace Cmuset\PgnParser\Tests\Validator;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Enum\Violation\MoveViolationEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Parser\PGNParser;
use Cmuset\PgnParser\Validator\GameValidator;
use PHPUnit\Framework\TestCase;

class GameValidatorTest extends TestCase
{
    private GameValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new GameValidator();
    }

    public function testValidMainLineReturnsEmpty(): void
    {
        $game = $this->createGameWithMoves(
            Position::fromFEN(PGNParser::INITIAL_FEN),
            $this->createMoveNode('e4', ColorEnum::WHITE),
            $this->createMoveNode('e5', ColorEnum::BLACK),
        );

        self::assertEmpty($this->validator->validate($game));
    }

    public function testWrongColorMoveDetected(): void
    {
        $game = $this->createGameWithMoves(
            Position::fromFEN(PGNParser::INITIAL_FEN),
            $this->createMoveNode('e5', ColorEnum::BLACK, 1),
        );

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::WRONG_COLOR_TO_MOVE, $violations, true));
    }

    public function testCaptureWithoutTargetDetected(): void
    {
        $position = Position::fromFEN('r3k3/8/8/8/8/8/8/4K3 b - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createMoveNode('Rxa1', ColorEnum::BLACK));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::NO_PIECE_TO_CAPTURE, $violations, true));
    }

    public function testTargetSquareOccupiedByOwnPieceDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(SquareEnum::G1, PieceEnum::WHITE_KNIGHT);
        $position->setPieceAt(SquareEnum::F3, PieceEnum::WHITE_PAWN);

        $game = $this->createGameWithMoves($position, $this->createMoveNode('Nf3', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::SQUARE_OCCUPIED_BY_OWN_PIECE, $violations, true));
    }

    public function testPieceNotFoundDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/8/8/4K3 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createMoveNode('Qh4', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::PIECE_NOT_FOUND, $violations, true));
    }

    public function testMultiplePiecesMatchDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(SquareEnum::D4, PieceEnum::WHITE_KNIGHT);
        $position->setPieceAt(SquareEnum::H4, PieceEnum::WHITE_KNIGHT);

        $game = $this->createGameWithMoves($position, $this->createMoveNode('Nf3', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::MULTIPLE_PIECES_MATCH, $violations, true));
    }

    public function testCastlingNotAllowedDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/8/8/4K2R w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createMoveNode('O-O', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::CASTLING_IS_NOT_ALLOWED, $violations, true));
    }

    public function testNextPositionInvalidDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(SquareEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(SquareEnum::D1, PieceEnum::WHITE_KING);
        $position->setPieceAt(SquareEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(SquareEnum::E2, PieceEnum::WHITE_PAWN);

        $game = $this->createGameWithMoves($position, $this->createMoveNode('e3', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::NEXT_POSITION_INVALID, $violations, true));
    }

    public function testMoveNotCheckDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/4P3/8/4KQ2 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createMoveNode('Qe2+', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::MOVE_NOT_CHECK, $violations, true));
    }

    public function testMoveNotCheckmateDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/4P3/8/4KQ2 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createMoveNode('Qe2#', ColorEnum::WHITE));

        $violations = $this->validator->validate($game);
        self::assertNotEmpty($violations);
        self::assertTrue(in_array(MoveViolationEnum::MOVE_NOT_CHECKMATE, $violations, true));
    }

    public function testOperaGameHasNoViolations(): void
    {
        $pgnFile = __DIR__ . '/../resources/opera_chesscom.pgn';
        $pgn = file_get_contents($pgnFile);

        if (false === $pgn) {
            self::fail('Failed to read PGN file: ' . $pgnFile);
        }

        $game = Game::fromPGN($pgn);

        $violations = $this->validator->validate($game);
        self::assertEmpty($violations);
    }

    private function createGameWithMoves(Position $position, MoveNode ...$nodes): Game
    {
        $game = new Game();
        $game->setInitialPosition($position);

        foreach ($nodes as $node) {
            $game->addMoveNode($node);
        }

        return $game;
    }

    private function createMoveNode(string $san, ColorEnum $color, int $moveNumber = 1): MoveNode
    {
        $node = new MoveNode();
        $node->setMove(Move::fromSAN($san, $color));
        $node->setColor($color);
        $node->setMoveNumber($moveNumber);

        return $node;
    }
}
