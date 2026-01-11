<?php

namespace Cmuset\PgnParser\Parser;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CommentAnchorEnum;
use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;

class PGNParser implements PGNParserInterface
{
    public const string INITIAL_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    public const string T_COMMENT = 'comment';
    public const string T_NAG = 'nag';
    public const string T_MOVENUM = 'movenum';
    public const string T_LPAR = 'lpar';
    public const string T_RPAR = 'rpar';
    public const string T_SAN = 'san';
    public const string T_RESULT = 'result';

    public function __construct(
        private readonly FENParserInterface $fenParser,
        private readonly SANParserInterface $sanParser,
    ) {
    }

    public static function create(): self
    {
        return new self(new FENParser(), new SANParser());
    }

    public function parse(string $pgn): Game
    {
        $tags = $this->extractTags($pgn);
        $fen = $tags['FEN'] ?? self::INITIAL_FEN;
        $initialPosition = $this->fenParser->parse($fen);

        $game = new Game();
        $game->setTags($this->extractTags($pgn));
        $game->setInitialPosition($initialPosition);
        $game->setResult($this->extractResult($pgn, $game->getTags()));

        $moveText = $this->extractMoveTextSection($pgn);
        $moveText = trim($moveText);
        $tokens = $this->tokenizeMoveText($moveText);

        $game->setMainLine($this->buildVariation($tokens));

        return $game;
    }

    private function extractTags(string $pgn): array
    {
        if (!preg_match('/^((?:\s*\[[^\]\n]+\]\s*)+)/u', $pgn, $m)) {
            return [];
        }
        $raw = trim($m[1]);
        $linesIn = preg_split('/\r?\n/', $raw);

        if (false === $linesIn) {
            return [];
        }

        $tags = [];
        foreach ($linesIn as $line) {
            if (preg_match('/^\s*\[([A-Za-z0-9_]+)\s+"(.*)"\]\s*$/u', $line, $mm)) {
                $tag = trim($mm[1]);
                $val = trim($mm[2]);
                $tags[$tag] = $val;
            }
        }

        return $tags;
    }

    private function extractResult(string $pgn, array $tags): ?ResultEnum
    {
        $resultTag = $tags['Result'] ?? null;

        if (null !== $resultTag) {
            try {
                return ResultEnum::from($resultTag);
            } catch (\Throwable) {
            }
        }

        if (preg_match('/(1-0|0-1|1\/2-1\/2|\*)\s*$/', $this->extractMoveTextSection($pgn), $m)) {
            return ResultEnum::from($m[1]);
        }

        return null;
    }

    private function extractMoveTextSection(string $pgn): string
    {
        if (preg_match('/^((?:\s*\[[^\]\n]+\]\s*)+)(.*)$/us', $pgn, $m)) {
            $moves = trim($m[2]);
        } else {
            $moves = $pgn;
        }
        $moves = preg_replace('/[ \t]+/', ' ', $moves) ?? $moves;

        return trim($moves);
    }

    private function buildVariation(array $tokens, int &$index = 0): Variation
    {
        $color = ColorEnum::WHITE;
        $moveNumber = 1;

        $tokensCount = count($tokens);
        $nextCommentAnchor = CommentAnchorEnum::PRE;
        $currentNode = null;
        $pendingPreComment = null;

        $variation = new Variation();
        while ($index < $tokensCount) {
            $token = $tokens[$index];
            ++$index;

            switch ($token['type']) {
                case self::T_MOVENUM:
                    // update color and move number; a movenum resets pre-comment context
                    $color = str_ends_with($token['value'], '...') ? ColorEnum::BLACK : ColorEnum::WHITE;
                    $moveNumber = (int) rtrim($token['value'], '.');
                    $nextCommentAnchor = CommentAnchorEnum::PRE;
                    break;

                case self::T_SAN:
                    $san = $token['value'];
                    $currentNode = new MoveNode();
                    $currentNode->setMove($this->sanParser->parse($san, $color));
                    $currentNode->setMoveNumber($moveNumber);

                    if (null !== $pendingPreComment) {
                        $currentNode->setComment($pendingPreComment, CommentAnchorEnum::PRE);
                        $pendingPreComment = null;
                    }

                    $variation->addNode($currentNode);

                    $nextCommentAnchor = CommentAnchorEnum::POST;
                    $color = ColorEnum::WHITE === $color ? ColorEnum::BLACK : ColorEnum::WHITE;
                    break;

                case self::T_NAG:
                    if ($currentNode) {
                        $currentNode->addNag((int) ltrim($token['value'], '$'));
                    }
                    break;

                case self::T_COMMENT:
                    $comment = trim($token['value']);

                    if (empty($comment)) {
                        break;
                    }

                    if (CommentAnchorEnum::PRE === $nextCommentAnchor) {
                        $pendingPreComment = $pendingPreComment
                            ? $pendingPreComment . ' ' . $comment
                            : $comment;
                        break;
                    }

                    if ($currentNode) {
                        $existing = $currentNode->getAfterMoveComment();
                        $currentNode->setAfterMoveComment($existing ? ($existing . ' ' . $comment) : $comment);
                        break;
                    }

                    // no move yet – treat as pre comment
                    $pendingPreComment = $pendingPreComment
                        ? $pendingPreComment . ' ' . $comment
                        : $comment;
                    break;

                case self::T_LPAR:
                    // build a variation line recursively starting at current index
                    $subVariation = $this->buildVariation($tokens, $index);

                    if ($currentNode && !$subVariation->isEmpty()) {
                        $currentNode->addVariation($subVariation);
                    }
                    break;

                case self::T_RESULT:
                case self::T_RPAR:
                    return $variation;

                default:
                    break;
            }
        }

        return $variation;
    }

    private function tokenizeMoveText(string $moveText): array
    {
        $tokens = [];
        $offset = 0;
        $len = strlen($moveText);

        $regex = '~\G
            \s+
          | \{(?P<brace>[^}]*)\}
          | ;(?P<sc>[^\n]*)
          | (?P<lpar>\()
          | (?P<rpar>\))
          | (?P<movenumEllipsis>\d+\.\.\.)
          | (?P<movenum>\d+\.)
          | (?P<nag>\$\d+)
          | (?P<result>1-0|0-1|1/2-1/2|\*)
          | (?P<castle>O-O-O|O-O|0-0-0|0-0)
          | (?P<san>[A-Za-z0-9=+#:/x-]+[!?+]*)
        ~xuA';

        while ($offset < $len) {
            if (!preg_match($regex, $moveText, $m, PREG_UNMATCHED_AS_NULL, $offset)) {
                ++$offset;
                continue;
            }

            $offset += strlen($m[0]);

            switch (true) {
                case null !== $m['brace']:
                    $tokens[] = ['type' => self::T_COMMENT, 'value' => trim($m['brace'])];
                    break;
                case null !== $m['sc']:
                    $tokens[] = ['type' => self::T_COMMENT, 'value' => trim($m['sc'])];
                    break;
                case null !== $m['lpar']:
                    $tokens[] = ['type' => self::T_LPAR];
                    break;
                case null !== $m['rpar']:
                    $tokens[] = ['type' => self::T_RPAR];
                    break;
                case null !== $m['movenumEllipsis']:
                    $tokens[] = ['type' => self::T_MOVENUM, 'value' => $m['movenumEllipsis']];
                    break;
                case null !== $m['movenum']:
                    $tokens[] = ['type' => self::T_MOVENUM, 'value' => $m['movenum']];
                    break;
                case null !== $m['nag']:
                    $tokens[] = ['type' => self::T_NAG, 'value' => $m['nag']];
                    break;
                case null !== $m['result']:
                    $tokens[] = ['type' => self::T_RESULT, 'value' => $m['result']];
                    break;
                case null !== $m['castle']:
                    $tokens[] = ['type' => self::T_SAN, 'value' => $m['castle']];
                    break;
                case null !== $m['san']:
                    $tokens[] = ['type' => self::T_SAN, 'value' => $m['san']];
                    break;
            }
        }

        return $tokens;
    }
}
