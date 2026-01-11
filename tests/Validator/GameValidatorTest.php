<?php

namespace Cmuset\PgnParser\Tests\Validator;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Variation;
use Cmuset\PgnParser\Parser\PGNParser;
use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;
use Cmuset\PgnParser\Validator\Enum\PositionViolationEnum;
use Cmuset\PgnParser\Validator\GameValidator;
use Cmuset\PgnParser\Validator\Model\GameViolation;
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
            $this->createNode('e4', ColorEnum::WHITE),
            $this->createNode('e5', ColorEnum::BLACK),
        );

        self::assertNull($this->validator->validate($game));
    }

    public function testWrongColorMoveDetected(): void
    {
        $game = $this->createGameWithMoves(
            Position::fromFEN(PGNParser::INITIAL_FEN),
            $this->createNode('e5', ColorEnum::BLACK),
        );

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::WRONG_COLOR_TO_MOVE, $violation->getMoveViolation());
        self::assertEquals('1... e5', $violation->getPath());
    }

    public function testCaptureWithoutTargetDetected(): void
    {
        $position = Position::fromFEN('r3k3/8/8/8/8/8/8/4K3 b - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createNode('Rxa1', ColorEnum::BLACK));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::NO_PIECE_TO_CAPTURE, $violation->getMoveViolation());
        self::assertEquals('1... Rxa1', $violation->getPath());
    }

    public function testTargetSquareOccupiedByOwnPieceDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(CoordinatesEnum::G1, PieceEnum::WHITE_KNIGHT);
        $position->setPieceAt(CoordinatesEnum::F3, PieceEnum::WHITE_PAWN);

        $game = $this->createGameWithMoves($position, $this->createNode('Nf3', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::SQUARE_OCCUPIED_BY_OWN_PIECE, $violation->getMoveViolation());
        self::assertEquals('1. Nf3', $violation->getPath());
    }

    public function testPieceNotFoundDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/8/8/4K3 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createNode('Qh4', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::PIECE_NOT_FOUND, $violation->getMoveViolation());
        self::assertEquals('1. Qh4', $violation->getPath());
    }

    public function testMultiplePiecesMatchDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(CoordinatesEnum::D4, PieceEnum::WHITE_KNIGHT);
        $position->setPieceAt(CoordinatesEnum::H4, PieceEnum::WHITE_KNIGHT);

        $game = $this->createGameWithMoves($position, $this->createNode('Nf3', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::MULTIPLE_PIECES_MATCH, $violation->getMoveViolation());
        self::assertEquals('1. Nf3', $violation->getPath());
    }

    public function testCastlingNotAllowedDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/8/8/4K2R w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createNode('O-O', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::CASTLING_IS_NOT_ALLOWED, $violation->getMoveViolation());
        self::assertEquals('1. O-O', $violation->getPath());
    }

    public function testNextPositionInvalidDetected(): void
    {
        $position = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $position->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $position->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_KING);
        $position->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $position->setPieceAt(CoordinatesEnum::E2, PieceEnum::WHITE_PAWN);

        $game = $this->createGameWithMoves($position, $this->createNode('e3', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals('1. e3', $violation->getPath());
        self::assertEquals(MoveViolationEnum::NEXT_POSITION_INVALID, $violation->getMoveViolation());
        self::assertContains(PositionViolationEnum::MULTIPLE_WHITE_KINGS, $violation->getPositionViolations());
    }

    public function testMoveNotCheckDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/4P3/8/4KQ2 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createNode('Qe2+', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::MOVE_NOT_CHECK, $violation->getMoveViolation());
        self::assertEquals('1. Qe2+', $violation->getPath());
    }

    public function testMoveNotCheckmateDetected(): void
    {
        $position = Position::fromFEN('4k3/8/8/8/8/4P3/8/4KQ2 w - - 0 1');
        $game = $this->createGameWithMoves($position, $this->createNode('Qe2#', ColorEnum::WHITE));

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::MOVE_NOT_CHECKMATE, $violation->getMoveViolation());
        self::assertEquals('1. Qe2#', $violation->getPath());
    }

    public function testOperaGameHasNoViolations(): void
    {
        $pgnFile = __DIR__ . '/../resources/opera_chesscom.pgn';
        $pgn = file_get_contents($pgnFile);

        if (false === $pgn) {
            self::fail('Failed to read PGN file: ' . $pgnFile);
        }

        $game = Game::fromPGN($pgn);

        $violation = $this->validator->validate($game);
        self::assertNull($violation);
    }

    public function testGameViolationInNestedVariation(): void
    {
        $position = Position::fromFEN(PGNParser::INITIAL_FEN);
        $game = $this->createGameWithMoves(
            $position,
            $this->createNode('e4', ColorEnum::WHITE),
            $this->createNode('e5', ColorEnum::BLACK),
            $lastNode = $this->createNode('d3', ColorEnum::WHITE),
        );

        $lastNode->addVariation(new Variation(
            $this->createNode('Nc3', ColorEnum::WHITE, 2),
            $this->createNode('Nc6', ColorEnum::BLACK, 2),
        ));

        $lastNode->addVariation(new Variation(
            $this->createNode('Nf3', ColorEnum::WHITE, 2),
            $this->createNode('Nc6', ColorEnum::BLACK, 2),
            $this->createNode('Bb5', ColorEnum::WHITE, 3),
            $this->createNode('Nf6', ColorEnum::BLACK, 3),
            $this->createNode('O-O', ColorEnum::WHITE, 4),
            $lastNode = $this->createNode('Be7', ColorEnum::BLACK, 4),
        ));

        $lastNode->addVariation(new Variation($this->createNode('O-O-O', ColorEnum::BLACK, 4)));

        self::assertEquals(
            '1. e4 e5 2. d3 (2. Nc3 Nc6) (2. Nf3 Nc6 3. Bb5 Nf6 4. O-O Be7 (4... O-O-O))',
            $game->getMainLine()->getPGN()
        );

        $violation = $this->validator->validate($game);
        self::assertInstanceOf(GameViolation::class, $violation);
        self::assertEquals(MoveViolationEnum::CASTLING_IS_NOT_ALLOWED, $violation->getMoveViolation());
        self::assertEquals('1. e4 e5 2. Nf3 Nc6 3. Bb5 Nf6 4. O-O O-O-O', $violation->getPath()); // path to O-O-O move
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

    private function createNode(string $san, ColorEnum $color, ?int $moveNumber = null): MoveNode
    {
        $node = new MoveNode();
        $node->setMove(Move::fromSAN($san, $color));
        $node->setMoveNumber($moveNumber);

        return $node;
    }
}
