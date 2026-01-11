<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;
use PHPUnit\Framework\TestCase;

class MoveNodeTest extends TestCase
{
    public function testAddVariation(): void
    {
        $node = new MoveNode();
        $variation = new MoveNode();
        $node->addVariation(new Variation($variation));

        self::assertCount(1, $node->getVariations());
    }

    public function testCommentAndNags(): void
    {
        $node = new MoveNode();
        $node->setComment('First');
        $node->setNags(['1']);
        $node->addNag(2);
        $node->addNag(2); // duplicate ignored

        self::assertSame('First', $node->getComment());
        self::assertSame([1, 2], $node->getNags());
    }

    public function testMoveData(): void
    {
        $node = new MoveNode();
        $move = new Move();
        $move->setPiece(PieceEnum::BLACK_PAWN);
        $node->setMove($move);
        $node->setMoveNumber(5);

        self::assertSame($move, $node->getMove());
        self::assertSame(5, $node->getMoveNumber());
        self::assertSame(ColorEnum::BLACK, $node->getColor());
    }
}
