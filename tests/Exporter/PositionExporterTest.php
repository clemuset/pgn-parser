<?php

namespace Cmuset\PgnParser\Tests\Exporter;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exporter\PositionExporter;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Parser\FENParser;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class PositionExporterTest extends TestCase
{
    private FENParser $fenParser;
    private PositionExporter $exporter;

    protected function setUp(): void
    {
        $this->fenParser = new FENParser();
        $this->exporter = new PositionExporter();
    }

    public function testExportInitialPositionRoundTrip(): void
    {
        $position = $this->fenParser->parse(PGNParser::INITIAL_FEN);
        $fen = $this->exporter->export($position);
        self::assertSame(PGNParser::INITIAL_FEN, $fen);
    }

    public function testExportEmptyBoardNoCastling(): void
    {
        $position = $this->fenParser->parse('8/8/8/8/8/8/8/8 w - - 0 1');
        $exported = $this->exporter->export($position);
        self::assertSame('8/8/8/8/8/8/8/8 w - - 0 1', $exported);
    }

    public function testExportCustomPositionWithEnPassant(): void
    {
        $position = new Position();

        $position->setPieceAt(SquareEnum::E4, PieceEnum::WHITE_KING);
        $position->setPieceAt(SquareEnum::E6, PieceEnum::BLACK_KING);
        $position->setPieceAt(SquareEnum::A2, PieceEnum::WHITE_PAWN);
        $position->setPieceAt(SquareEnum::B7, PieceEnum::BLACK_PAWN);
        $position->setSideToMove(ColorEnum::BLACK);
        $position->setCastlingRights([]);
        $position->setEnPassantTarget(SquareEnum::D6);
        $position->setHalfmoveClock(7);
        $position->setFullmoveNumber(23);

        $fen = $this->exporter->export($position);
        self::assertSame('8/1p6/4k3/8/4K3/8/P7/8 b - d6 7 23', $fen);
    }
}
