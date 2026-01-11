<?php

namespace Cmuset\PgnParser\Tests\Model;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Position;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function testTagsLifecycle(): void
    {
        $game = new Game();
        $game->setTag('Event', 'Test');
        self::assertSame('Test', $game->getTag('Event'));
        $game->removeTag('Event');
        self::assertNull($game->getTag('Event'));
    }

    public function testInitialPositionAndRoot(): void
    {
        $game = new Game();
        $position = new Position();
        $node = new MoveNode();
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $node->setMove($move);
        $node->setMoveNumber(1);
        $game->setInitialPosition($position);
        $game->addMoveNode($node);

        self::assertSame($position, $game->getInitialPosition());
        self::assertSame($node, $game->getMainLine()['1.']);
    }

    public function testResultAssignment(): void
    {
        $game = new Game();
        $game->setResult(ResultEnum::WHITE_WIN);
        self::assertSame(ResultEnum::WHITE_WIN, $game->getResult());
    }
}
