<?php

namespace Cmuset\PgnParser\Tests\Parser;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Parser\Exception\FENParsingException;
use Cmuset\PgnParser\Parser\FENParser;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class FENParserTest extends TestCase
{
    private FENParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FENParser();
    }

    public function testParseInitialFEN(): void
    {
        $position = $this->parser->parse(PGNParser::INITIAL_FEN);

        self::assertSame(ColorEnum::WHITE, $position->getSideToMove());
        self::assertSame(0, $position->getHalfmoveClock());
        self::assertSame(1, $position->getFullmoveNumber());
        self::assertNull($position->getEnPassantTarget());
        $rights = $position->getCastlingRights();
        self::assertContains(CastlingEnum::WHITE_KINGSIDE, $rights);
        self::assertContains(CastlingEnum::BLACK_QUEENSIDE, $rights);

        $pieceCount = 0;
        foreach ($position->getSquares() as $sq) {
            if (!$sq->isEmpty()) {
                ++$pieceCount;
            }
        }
        self::assertSame(32, $pieceCount);
    }

    public function testParseEmptyBoardNoCastling(): void
    {
        $fen = '8/8/8/8/8/8/8/8 w - - 5 10';
        $position = $this->parser->parse($fen);
        self::assertSame(ColorEnum::WHITE, $position->getSideToMove());
        self::assertSame(5, $position->getHalfmoveClock());
        self::assertSame(10, $position->getFullmoveNumber());
        self::assertSame([], $position->getCastlingRights());
    }

    public function testParseWithEnPassantTarget(): void
    {
        $fen = 'rnbqkbnr/pppppppp/8/8/3Pp3/8/PPP1PPPP/RNBQKBNR b KQkq d3 0 2';
        $position = $this->parser->parse($fen);
        self::assertSame(ColorEnum::BLACK, $position->getSideToMove());
        self::assertNotNull($position->getEnPassantTarget());
        self::assertSame('d3', $position->getEnPassantTarget()->value);
    }

    public function testInvalidFenThrows(): void
    {
        $this->expectException(FENParsingException::class);
        $this->parser->parse('8/8/8/8/8/8/8/8 w - - 5'); // missing fullmove number
    }
}
