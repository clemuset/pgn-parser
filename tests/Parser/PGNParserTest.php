<?php

namespace Cmuset\PgnParser\Tests\Parser;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class PGNParserTest extends TestCase
{
    private PGNParser $parser;

    protected function setUp(): void
    {
        $this->parser = PGNParser::create();
    }

    public function testParseGameWithTagsAndResultInTag(): void
    {
        $pgn = <<<'PGN'
[Event "Test"]
[Result "1-0"]

1. e4 e5 2. Nf3 Nc6 3. Bb5 *
PGN;
        $game = $this->parser->parse($pgn);
        $mainLine = $game->getMainLine();

        self::assertSame('Test', $game->getTag('Event'));
        self::assertSame(ResultEnum::WHITE_WIN, $game->getResult());
        self::assertSame(ColorEnum::WHITE, $mainLine['1.']->getColor());

        // Traverse first three moves
        $n1 = $mainLine['1.']; // 1. e4
        $n2 = $mainLine['1...']; // ... e5
        $n3 = $mainLine['2.']; // 2. Nf3
        self::assertSame(1, $n1->getMoveNumber());
        self::assertSame('e4', $n1->getMove()->getSAN());
        self::assertSame(1, $n2->getMoveNumber());
        self::assertSame('e5', $n2->getMove()->getSAN());
        self::assertSame(2, $n3->getMoveNumber());
        self::assertSame('Nf3', $n3->getMove()->getSAN());
    }

    public function testParseGameWithoutTagsResultAtEnd(): void
    {
        $pgn = '1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 1-0';
        $game = $this->parser->parse($pgn);

        self::assertSame(ResultEnum::WHITE_WIN, $game->getResult());
        self::assertEmpty($game->getTags());
    }

    public function testParseCommentsAndNagsAndVariation(): void
    {
        $pgn = <<<'PGN'
[Event "VarTest"]

1. e4 {Central control} e5 2. Nf3 $1 (2... Nc6 3. Bb5) 2... d6 *
PGN;
        $game = $this->parser->parse($pgn);
        $mainLine = $game->getMainLine();

        // Root -> 1.e4
        $e4 = $mainLine['1.'];
        self::assertSame('Central control', $e4->getComment());
        $nf3 = $mainLine['2.'];
        self::assertTrue(in_array(1, $nf3->getNags()));
        $variationNodes = $nf3->getVariations();
        self::assertCount(1, $variationNodes);
        $variation = $variationNodes[0];
        // Variation root has its own mainline (2... Nc6 3. Bb5)
        $nc6 = $variation['2...'] ?? null;
        self::assertNotNull($nc6);
        self::assertSame(2, $nc6->getMoveNumber());
        self::assertSame(ColorEnum::BLACK, $nc6->getColor());
    }
}
