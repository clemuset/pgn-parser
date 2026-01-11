<?php

namespace Cmuset\PgnParser\Tests\Exporter;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Exporter\GameExporter;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\MoveNode;
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

        // 1. e4
        $n1 = new MoveNode();
        $m1 = new Move();
        $m1->setPiece(PieceEnum::WHITE_PAWN);
        $m1->setTo(CoordinatesEnum::E4);
        $n1->setMove($m1);
        $n1->setMoveNumber(1);
        $game->addMoveNode($n1);

        // ... e5 (black reply)
        $n2 = new MoveNode();
        $m2 = new Move();
        $m2->setPiece(PieceEnum::BLACK_PAWN);
        $m2->setTo(CoordinatesEnum::E5);
        $n2->setMove($m2);
        $n2->setMoveNumber(1);
        $game->addMoveNode($n2);

        // 2. Nf3
        $n3 = new MoveNode();
        $m3 = new Move();
        $m3->setPiece(PieceEnum::WHITE_KNIGHT);
        $m3->setTo(CoordinatesEnum::F3);
        $n3->setMove($m3);
        $n3->setMoveNumber(2);
        $game->addMoveNode($n3);

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
        $n1 = new MoveNode();
        $m1 = new Move();
        $m1->setPiece(PieceEnum::WHITE_PAWN);
        $m1->setTo(CoordinatesEnum::E4);
        $n1->setMove($m1);
        $n1->setMoveNumber(1);
        $game->addMoveNode($n1);

        // 1... e5
        $n2 = new MoveNode();
        $m2 = new Move();
        $m2->setPiece(PieceEnum::BLACK_PAWN);
        $m2->setTo(CoordinatesEnum::E5);
        $n2->setMove($m2);
        $n2->setMoveNumber(1);
        $game->addMoveNode($n2);

        // Variation after 1.e4 e5: 1... c5
        $varMoveNode = new MoveNode();
        $varMove = new Move();
        $varMove->setPiece(PieceEnum::BLACK_PAWN);
        $varMove->setTo(CoordinatesEnum::C5);
        $varMoveNode->setMove($varMove);
        $varMoveNode->setMoveNumber(1);
        $n2->addVariation([$varMoveNode]);

        $pgn = $this->exporter->export($game);

        // Variation should reflect exporter behavior: only SAN without move number
        self::assertStringContainsString('(1... c5)', $pgn);
    }

    public function testRoundTripExportIdempotent(): void
    {
        $pgn = <<<'PGN'
[Event "Paris"]
[Site "Paris FRA"]
[Date "1858.??.??"]
[Round "?"]
[White "Morhpy, Paul"]
[Black "Allies"]
[Result "1-0"]
[EventDate "?"]
[ECO "C41"]
[WhiteElo "?"]
[BlackElo "?"]
[PlyCount "33"]
[Link "https://www.chess.com/blog/SamCopeland/paul-morphys-opera-game-every-move-explained-for-chess-beginners"]

{This famous game is known as the \"Opera Game\" as it was played informally at
the Paris Opera between Paul Morphy and the Duke of Brunswick and Count Isouard
who played in consultation.} 1. e4 {Seizes the center. Frees the bishop and
queen.} 1... e5 {Equally good for the same reasons.} 2. Nf3 {The knight develops
with \"tempo,\" attacking e5. Because the kings' pawns are undefended on e4 and
e5, attacks and counterattacks are possible, and the game is sharper than after
1.d4 d5 when the queens defend the pawns, slowing down attacks.} 2... d6 $6
{Black defends e5, BUT this move blocks the bishop on f8. This opening, \"The
Philidor Defense\" is not bad, but it is considered inferior to 2...Nc6 which
defends the e5-pawn without blocking the bishop on f8.} (2... Nc6) 3. d4 {Morphy
plays the most active move, freeing the bishop on c1 and attacking the pawn on
e5.} 3... Bg4 $2 {Black uses a tactic, a \"pin\" on the knight on f3 which cannot
move without losing the queen on d1, to defend the pawn on e5, but the tactic is
flawed. It was better to play ...exd4 although White's position is much better.}
(3... exd4 4. Qxd4) 4. dxe5 {Morphy has an idea to win a pawn.} 4... Bxf3 {This
is the only way to avoid losing a pawn on e5, but it helps Morphy develop the
queen and create threats against f7.} (4... dxe5 $2 {Recapturing the pawn.} 5.
Qxd8+ {Trading queens at just the right time.} 5... Kxd8 {The only legal move -
therefore the \"best\" move.} 6. Nxe5 {By trading queens, Morphy would \"unpin\"
the knight on f3 and allow it top capture on e5.}) 5. Qxf3 {Recapturing and
activating the queen.} 5... dxe5 {Regaining the pawn.} 6. Bc4 {Morphy develops
the bishop and threatens checkmate with 7.Qxf7#} 6... Nf6 {Black develops the
knight and stops the checkmate on f7. Can you now find a move that creates two
threats for White $2} 7. Qb3 $1 {Morphy attacks f7 AND b7. The arrangement with the
bishop on c4 being supported by the queen on f3 is called a \"battery\" - a
lineup of two linear pieces.} 7... Qe7 {Black can't defend both threats so Black
defends the more dangerous one, the attack on f7. Black also has an idea to
trade queens if Morphy captures on b7, BUT the queen is now blocking the bishop
on f8, and it is hard for Black to develop the kingside pieces and castle.} 8.
Nc3 {Morphy develops a queen and stops ...Qb4+ trading queens.} (8. Qxb7 {wins a
free pawn. This also looks like it traps the rook on a8, but Black has a
defense.} 8... Qb4+ {This check trades queens. White is still winning, but
Morphy wanted to keep attacking and did not choose this path which trades
queens, making it harder to attack.} 9. Qxb4 Bxb4+) (8. Bxf7+ $1 {This is a very
nice trick which wins more material.} 8... Qxf7 {The only way to capture on f7.}
9. Qxb7 {After capturing on f7, the black queen can no longer reach b4 to trade
queens which means there is no trick to save the trapped rook on a8.}) 8... c6
{Black finds the best move. This move allows the queen to defend the pawn on b7,
AND the pawn defends the squares b5 and d5 so that White's knight can't advance
to these squares.} (8... Qb4 9. Bxf7+) 9. Bg5 {Morphy develops his last minor
piece (minor pieces are bishops and knights), makes a pin on the f8-knight which
can't move without allowing White to capture the queen on e7, and gets to ready
to castle queenside. One move accomplishes a lot $1} 9... b5 $2 {It's hard to
suggest a good move for Black. This proves to be a mistake because Morphy's
sacrifice on the next move is very strong, but it's hard to develop any black
pieces.} (9... Na6 {White can take this knight with Bxa6 and break up Black's
queenside pawns, but maybe this was Black's best move, developing a piece.}) 10.
Nxb5 $1 {Morphy has three minor pieces developed as well as his queen, AND he's
ready to castle and get his rooks into the game. That lead in development
justifies his piece sacrifice which unleashes his remaining pieces and traps
Black in a crossfire in the center of the board.} (10. Bd3 $2 {If Morphy had
retreated, he would have lost the initiative, and Black could have developed
with ...Nbd7, solving most of his problems.} 10... Nbd7) 10... cxb5 {Black
should capture the knight as otherwise he has lost a pawn for nothing. Now he at
least has an extra knight for his suffering.} 11. Bxb5+ {Morphy captures back
and points the powerful bishop on b5 right at the queen.} 11... Nbd7 {Black
develops the knight and blocks the check.} (11... Kd8 {Moving the king means the
king can NEVER castle, and Morphy can just continue bringing more pieces into
the attack with check with 0-0-0+}) 12. O-O-O $1 {Morphy castles AND places his
rook on d1. He immediately threatens to win material by capturing on d7 with the
rook or the bishop as the knight on f6 is still pinned and is not a helpful
defender.} 12... Rd8 {Black develops his a8-rook and defends the knight on d7.
This was Black's only way to defend d7.} 13. Rxd7 $1 {A brilliant move $1 Morphy
wants to include his last piece, the rook on h1, but he displays no patience (a
good habit for a player who has sacrificed, time is of the essence) and
sacrifices to bring the rook to d1 as fast as possible.} 13... Rxd7 {The knight
on f6 is still pinned so this was the only way to recapture that did not lose
material.} 14. Rd1 {Morphy's final piece develops with another tempo move, a
threat to yet again capture on d7.} 14... Qe6 {Black unpins the knight on f6 so
that it can finally defend d7.} 15. Bxd7+ {Morphy has sniffed out a checkmating
idea...} 15... Nxd7 {The only recapture which doesn't allow White to capture the
black queen.} 16. Qb8+ $3 {A brilliant finish $1 Checkmate wins the game, and chess
players must not be afraid to sacrifice even their best piece, the queen, if
checkmate can be forced.} 16... Nxb8 {The only legal move.} 17. Rd8# {Checkmate $1
This checkmate in which the bishop defends the rook like this is known as
\"Morphy's Mate\" in memory of this brilliant \"Opera Game.\"} 1-0
PGN;

        $game1 = $this->parser->parse($pgn);
        $export1 = $this->exporter->export($game1);

        $game2 = $this->parser->parse($export1);
        $export2 = $this->exporter->export($game2);

        self::assertSame($export1, $export2);
    }
}
