<?php

namespace Cmuset\PgnParser\Tests\Exporter;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Exporter\GameExporter;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class GameExporterTest extends TestCase
{
    private PGNParser $parser;
    private GameExporter $exporter;

    protected function setUp(): void
    {
        $this->parser = PGNParser::create();
        $this->exporter = GameExporter::create();
    }

    public function testExportSimpleGameNoResult(): void
    {
        $game = new Game();
        $game->setTag('Event', 'Test');
        $game->setTag('Site', 'Somewhere');

        $game->addMoveNodes('e4', 'e5', 'Nf3');

        $pgn = $this->exporter->export($game);

        self::assertStringContainsString('[Event "Test"]', $pgn);
        self::assertStringContainsString('[Site "Somewhere"]', $pgn);
        // Expect standard PGN formatting without repeating move number for black reply
        self::assertStringContainsString('1. e4 e5 2. Nf3 *', $pgn);
    }

    public function testExportWithVariation(): void
    {
        $game = new Game();

        // 1. e4
        $n1 = new MoveNode(Move::fromSAN('e4'));
        $game->addMoveNode($n1);

        // 1... e5
        $n2 = new MoveNode(Move::fromSAN('e5', ColorEnum::BLACK));
        $game->addMoveNode($n2);

        // Variation after 1.e4 e5: 1... c5
        $varMoveNode = new MoveNode(Move::fromSAN('c5', ColorEnum::BLACK));
        $n2->addVariation(new Variation($varMoveNode));

        $pgn = $this->exporter->export($game);

        // Variation should reflect exporter behavior: only SAN without move number
        self::assertStringContainsString('(1... c5)', $pgn);
    }

    public function testRoundTripExportIdempotent(): void
    {
        $pgn = file_get_contents(__DIR__ . '/../resources/opera_chesscom.pgn');

        if (false === $pgn) {
            self::fail('Failed to read PGN file for round-trip test.');
        }

        $game1 = $this->parser->parse($pgn);
        $export1 = $this->exporter->export($game1);

        $game2 = $this->parser->parse($export1);
        $export2 = $this->exporter->export($game2);

        self::assertSame($export1, $export2);
    }
}
