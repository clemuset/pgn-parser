<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{
    public function testConstructorWithNodes(): void
    {
        $node1 = new MoveNode();
        $move1 = new Move();
        $move1->setPiece(PieceEnum::WHITE_PAWN);
        $node1->setMove($move1);

        $node2 = new MoveNode();
        $move2 = new Move();
        $move2->setPiece(PieceEnum::BLACK_PAWN);
        $node2->setMove($move2);

        $variation = new Variation($node1, $node2);

        self::assertCount(2, $variation);
        self::assertFalse($variation->isEmpty());
    }

    public function testEmptyVariation(): void
    {
        $variation = new Variation();

        self::assertCount(0, $variation);
        self::assertTrue($variation->isEmpty());
        self::assertNull($variation->getLastNode());
    }

    public function testAddSingleNode(): void
    {
        $variation = new Variation();
        $node = new MoveNode();
        $move = Move::fromSAN('e4');
        $node->setMove($move);

        $variation->addNode($node);

        self::assertCount(1, $variation);
        self::assertSame($node, $variation->getLastNode());
        self::assertSame(1, $node->getMoveNumber());
        self::assertSame('e4', $variation->getIdentifier());
    }

    public function testAddMultipleNodes(): void
    {
        $variation = new Variation();
        $node1 = new MoveNode();
        $move1 = new Move();
        $move1->setPiece(PieceEnum::WHITE_PAWN);
        $node1->setMove($move1);

        $node2 = new MoveNode();
        $move2 = new Move();
        $move2->setPiece(PieceEnum::BLACK_PAWN);
        $node2->setMove($move2);

        $variation->addNodes($node1, $node2);

        self::assertCount(2, $variation);
        self::assertSame($node2, $variation->getLastNode());
    }

    public function testMoveNumberAutoIncrement(): void
    {
        $variation = new Variation();

        $whiteNode1 = new MoveNode();
        $whiteMove1 = new Move();
        $whiteMove1->setPiece(PieceEnum::WHITE_PAWN);
        $whiteNode1->setMove($whiteMove1);

        $blackNode1 = new MoveNode();
        $blackMove1 = new Move();
        $blackMove1->setPiece(PieceEnum::BLACK_PAWN);
        $blackNode1->setMove($blackMove1);

        $whiteNode2 = new MoveNode();
        $whiteMove2 = new Move();
        $whiteMove2->setPiece(PieceEnum::WHITE_KNIGHT);
        $whiteNode2->setMove($whiteMove2);

        $variation->addNodes($whiteNode1, $blackNode1, $whiteNode2);

        self::assertSame(1, $whiteNode1->getMoveNumber());
        self::assertSame(1, $blackNode1->getMoveNumber());
        self::assertSame(2, $whiteNode2->getMoveNumber());
    }

    public function testArrayAccess(): void
    {
        $variation = new Variation();
        $node = new MoveNode();
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $node->setMove($move);

        $variation->addNode($node);

        self::assertTrue(isset($variation['1.']));
        self::assertSame($node, $variation['1.']);
        self::assertNull($variation['2.']);
    }

    public function testArrayAccessForBlackMove(): void
    {
        $variation = new Variation();

        $whiteNode = new MoveNode();
        $whiteMove = new Move();
        $whiteMove->setPiece(PieceEnum::WHITE_PAWN);
        $whiteNode->setMove($whiteMove);

        $blackNode = new MoveNode();
        $blackMove = new Move();
        $blackMove->setPiece(PieceEnum::BLACK_PAWN);
        $blackNode->setMove($blackMove);

        $variation->addNodes($whiteNode, $blackNode);

        self::assertTrue(isset($variation['1.']));
        self::assertTrue(isset($variation['1...']));
        self::assertSame($whiteNode, $variation['1.']);
        self::assertSame($blackNode, $variation['1...']);
    }

    public function testIterator(): void
    {
        $node1 = new MoveNode();
        $move1 = new Move();
        $move1->setPiece(PieceEnum::WHITE_PAWN);
        $node1->setMove($move1);

        $node2 = new MoveNode();
        $move2 = new Move();
        $move2->setPiece(PieceEnum::BLACK_PAWN);
        $node2->setMove($move2);

        $variation = new Variation($node1, $node2);

        $count = 0;
        foreach ($variation as $key => $node) {
            ++$count;
            self::assertInstanceOf(MoveNode::class, $node);
        }
        self::assertSame(2, $count);
    }

    public function testClone(): void
    {
        $node = new MoveNode();
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $node->setMove($move);

        $variation = new Variation($node);
        $cloned = clone $variation;

        self::assertCount(1, $cloned);
        self::assertNotSame($variation['1.'], $cloned['1.']);
    }

    public function testOffsetSetAndUnset(): void
    {
        $variation = new Variation();
        $node = new MoveNode();

        $variation['1.'] = $node;
        self::assertTrue(isset($variation['1.']));
        self::assertSame($node, $variation['1.']);

        unset($variation['1.']);
        self::assertFalse(isset($variation['1.']));
    }

    public function testGetPGN(): void
    {
        $variation = new Variation();
        $node1 = new MoveNode();
        $node1->setMove(Move::fromSAN('e4'));

        $node2 = new MoveNode();
        $node2->setMove(Move::fromSAN('e5', ColorEnum::BLACK));

        $node3 = new MoveNode();
        $node3->setMove(Move::fromSAN('Nf3'));

        $variation->addNodes($node1, $node2, $node3);

        self::assertSame('1. e4 e5 2. Nf3', $variation->getPGN());
    }
}
