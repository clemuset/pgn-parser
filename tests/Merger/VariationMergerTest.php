<?php

namespace Cmuset\PgnParser\Tests\Merger;

use Cmuset\PgnParser\Merger\VariationMerger;
use Cmuset\PgnParser\Model\Variation;
use PHPUnit\Framework\TestCase;

class VariationMergerTest extends TestCase
{
    private VariationMerger $merger;

    protected function setUp(): void
    {
        $this->merger = VariationMerger::create();
    }

    public function testMergeSingleVariationIntoMainLine(): void
    {
        $mainLine = new Variation('e4', 'e5');
        self::assertEquals('1. e4 e5', $mainLine->getLitePGN());

        $variation = new Variation('Nf3', 'Nc6');
        self::assertEquals('1. Nf3 Nc6', $variation->getLitePGN());

        $this->merger->merge($mainLine, $variation);
        self::assertEquals('1. e4 (1. Nf3 Nc6) 1... e5', $mainLine->getLitePGN());
    }

    public function testMergeIdenticalMovesContinuesMerge(): void
    {
        $mainLine = new Variation('e4', 'e5');
        $variation = new Variation('e4', 'e5', 'Nf3');

        $this->merger->merge($mainLine, $variation);

        self::assertEquals('1. e4 e5 2. Nf3', $mainLine->getLitePGN());
    }

    public function testMergeMultipleVariations(): void
    {
        $mainLine = Variation::fromPGN('1. e4 e5 2. Nf3 Nc6 3. Bb5 a6');
        $variation1 = Variation::fromPGN('1. e4 e5 2. d4 exd4');

        // In this variation, sub-variation is the main line and must be merged correctly
        $variation2 = Variation::fromPGN('1. e4 c5 (1... e5 2. Nf3 Nc6 3. Bb5 a6)');

        $variation3 = Variation::fromPGN('1. e4 e5 2. d4 d5 3. Bb5+');

        $this->merger->merge($mainLine, $variation1, $variation2, $variation3);

        self::assertEquals('1. e4 e5 (1... c5) 2. Nf3 (2. d4 exd4 (2... d5 3. Bb5+)) 2... Nc6 3. Bb5 a6', $mainLine->getLitePGN());
    }
}
