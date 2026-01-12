<?php

namespace Cmuset\PgnParser\Tests\Splitter;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;
use Cmuset\PgnParser\Splitter\VariationSplitter;
use PHPUnit\Framework\TestCase;

class VariationSplitterTest extends TestCase
{
    private VariationSplitter $splitter;

    protected function setUp(): void
    {
        $this->splitter = VariationSplitter::create();
    }

    public function testSplitGameWithoutVariations(): void
    {
        $game = new Game();
        $game->setTag('Event', 'Test');
        $game->addMoveNodes('e4', 'e5', 'Nf3', 'Nc6');

        $variations = $this->splitter->split($game->getMainLine());

        self::assertCount(1, $variations);
        self::assertCount(4, $variations[0]);
    }

    public function testSplitGameWithOneVariation(): void
    {
        $game = new Game();
        $game->setTag('Event', 'Test');

        // 1. e4
        $n1 = new MoveNode(Move::fromSAN('e4'));
        $game->addMoveNode($n1);

        // 1... e5
        $n2 = new MoveNode(Move::fromSAN('e5', ColorEnum::BLACK));
        $game->addMoveNode($n2);

        // Variation after 1.e4: 1... c5
        $varNode = new MoveNode(Move::fromSAN('c5', ColorEnum::BLACK));
        $n2->addVariation(new Variation($varNode));

        // 2. Nf3
        $game->addMoveNode('Nf3');

        self::assertEquals('1. e4 e5 (1... c5) 2. Nf3', $game->getMainLine()->getLitePGN());

        $variations = $this->splitter->split($game);

        // We should have 2 games: main line + 1 variation
        self::assertCount(2, $variations);

        // First game should have the main line without variations
        self::assertEquals('1. e4 e5 2. Nf3', $variations[0]->getLitePGN());
        self::assertCount(3, $variations[0]);

        // Second game should have the variation
        self::assertEquals('1. e4 c5', $variations[1]->getLitePGN());
        self::assertSame('c5', $variations[1]->getLastNode()?->getMove()?->getSAN());
    }

    public function testSplitGameWithNestedVariations(): void
    {
        $game = new Game();
        $game->setTag('Event', 'Test Nested');

        // 1. e4
        $n1 = new MoveNode(Move::fromSAN('e4'));
        $game->addMoveNode($n1);

        // 1... e5
        $n2 = new MoveNode(Move::fromSAN('e5', ColorEnum::BLACK));
        $game->addMoveNode($n2);

        // Main variation after 1.e4: 1... c5
        $var1Node1 = new MoveNode(Move::fromSAN('c5', ColorEnum::BLACK));
        $variation1 = new Variation($var1Node1);
        $n1->addVariation($variation1);

        // Sub-variation within the first variation: 1... d5
        $variation2 = new Variation('d5');
        $var1Node1->addVariation($variation2);

        // 2. Nf3
        $game->addMoveNode('Nf3');

        $variations = $this->splitter->split($game);

        // We should have 3 games: main line + 2 variations
        self::assertCount(3, $variations);

        // First is the main line
        self::assertCount(3, $variations[0]);

        // Second is the first variation (1... c5)
        self::assertCount(1, $variations[1]);
        self::assertSame('c5', $variations[1]->getLastNode()?->getMove()?->getSAN());

        // Third is the nested variation (1... d5)
        self::assertCount(1, $variations[2]);
        self::assertSame('d5', $variations[2]->getLastNode()?->getMove()?->getSAN());
    }

    public function testSplitComplexVariation(): void
    {
        $variation = Variation::fromPGN('1. d4 d5 2. Bf4 (2. c4 c6 (2... d6 3. Nf3 Nf6 (3... Nc6) 4. Nc3) 3. Nc3) 2... c5 (2... c6)');

        $variations = $variation->split();

        self::assertCount(5, $variations);
        self::assertEquals('1. d4 d5 2. Bf4 c5', $variations[0]->getLitePGN());
        self::assertEquals('1. d4 d5 2. c4 c6 3. Nc3', $variations[1]->getLitePGN());
        self::assertEquals('1. d4 d5 2. c4 d6 3. Nf3 Nf6 4. Nc3', $variations[2]->getLitePGN());
        self::assertEquals('1. d4 d5 2. c4 d6 3. Nf3 Nc6', $variations[3]->getLitePGN());
        self::assertEquals('1. d4 d5 2. Bf4 c6', $variations[4]->getLitePGN());
    }

    public function testSplitVariationWithoutSubVariations(): void
    {
        $variation = new Variation();
        $n1 = new MoveNode(Move::fromSAN('e4'));
        $n2 = new MoveNode(Move::fromSAN('e5', ColorEnum::BLACK));

        $variation->addNode($n1);
        $variation->addNode($n2);

        $variations = $this->splitter->split($variation);

        self::assertCount(1, $variations);
        self::assertCount(2, $variations[0]);
    }

    public function testSplitVariationWithSubVariations(): void
    {
        $mainVariation = new Variation();

        // 1. e4
        $n1 = new MoveNode(Move::fromSAN('e4'));
        $mainVariation->addNode($n1);

        // 1... e5
        $n2 = new MoveNode(Move::fromSAN('e5', ColorEnum::BLACK));
        $mainVariation->addNode($n2);

        // Variation after 1.e4: 1... c5
        $subVar1 = new Variation(new MoveNode(Move::fromSAN('c5', ColorEnum::BLACK)));
        $n1->addVariation($subVar1);

        // Another variation after 1.e4: 1... d5
        $subVar2 = new Variation(new MoveNode(Move::fromSAN('d5', ColorEnum::BLACK)));
        $n1->addVariation($subVar2);

        $variations = $this->splitter->split($mainVariation);

        // We should have 3 variations: main + 2 sub-variations
        self::assertCount(3, $variations);

        // First variation is the main line
        self::assertCount(2, $variations[0]);

        // Second variation
        self::assertCount(1, $variations[1]);
        self::assertSame('c5', $variations[1]->getLastNode()?->getMove()?->getSAN());

        // Third variation
        self::assertCount(1, $variations[2]);
        self::assertSame('d5', $variations[2]->getLastNode()?->getMove()?->getSAN());
    }
}
