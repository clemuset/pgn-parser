<?php

namespace Cmuset\PgnParser\Parser;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exception\FENParsingException;
use Cmuset\PgnParser\Model\Position;

class FENParser implements FENParserInterface
{
    public const string FEN_PATTERN = '/^((([rnbqkpRNBQKP1-8]{1,8})\/){7}([rnbqkpRNBQKP1-8]{1,8}))\s([bw])\s(-|K?Q?k?q?)\s(-|[a-h][36])\s(\d+)\s(\d+)$/';

    public function parse(string $fen): Position
    {
        $fen = trim($fen);

        if (1 !== preg_match(self::FEN_PATTERN, $fen)) {
            throw new FENParsingException('Invalid FEN string');
        }

        $position = new Position();

        foreach ($this->extractPiecesPosition($fen) as $piece) {
            $position->setPieceAt($piece[0], $piece[1]);
        }

        $position->setSideToMove(ColorEnum::from($this->extractSideToMove($fen)));
        $position->setCastlingRights($this->extractCastlingRights($fen));
        $position->setEnPassantTarget(SquareEnum::tryFrom($this->extractEnPassantTarget($fen)));
        $position->setHalfmoveClock($this->extractHalfmoveClock($fen));
        $position->setFullmoveNumber($this->extractFullmoveNumber($fen));

        return $position;
    }

    private function extractRows(string $fen): array
    {
        $parts = explode(' ', $fen);

        return explode('/', trim($parts[0]));
    }

    private function extractPiecesPosition(string $fen): array
    {
        $rows = $this->extractRows($fen);
        $pieces = [];
        for ($rank = 8; $rank >= 1; --$rank) {
            $file = 'a';
            $row = trim($rows[8 - $rank]);
            foreach (str_split($row) as $char) {
                if (is_numeric($char)) {
                    $file = chr(ord($file) + (int) $char);
                } else {
                    $pieces[] = [SquareEnum::from($file . $rank), PieceEnum::from($char)];
                    ++$file;
                }
            }
        }

        return $pieces;
    }

    private function extractSideToMove(string $fen): ?string
    {
        $parts = explode(' ', $fen);

        return $parts[1] ? trim($parts[1]) : null;
    }

    private function extractCastlingRightsPart(string $fen): ?string
    {
        $parts = explode(' ', $fen);

        return $parts[2] ? trim($parts[2]) : null;
    }

    private function extractCastlingRights(string $fen): array
    {
        $castlingPart = $this->extractCastlingRightsPart($fen);
        $castlingRights = [];

        if ('-' !== $castlingPart) {
            foreach (str_split($castlingPart) as $char) {
                $castlingRights[] = CastlingEnum::from($char);
            }
        }

        return $castlingRights;
    }

    private function extractEnPassantTarget(string $fen): ?string
    {
        $parts = explode(' ', $fen);

        return $parts[3] ? trim($parts[3]) : null;
    }

    private function extractHalfmoveClock(string $fen): ?int
    {
        $parts = explode(' ', $fen);

        return isset($parts[4]) ? (int) $parts[4] : null;
    }

    private function extractFullmoveNumber(string $fen): ?int
    {
        $parts = explode(' ', $fen);

        return isset($parts[5]) ? (int) $parts[5] : null;
    }
}
