<?php

namespace Cmuset\PgnParser\Exporter;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;

class PositionExporter implements PositionExporterInterface
{
    public function export(Position $position): string
    {
        $rows = [];
        for ($rank = 8; $rank >= 1; --$rank) {
            $row = '';
            $emptyCount = 0;
            for ($file = 'a'; $file <= 'h'; ++$file) {
                $square = $file . $rank;
                $piece = $position->getPieceAt(SquareEnum::from($square));

                if (null === $piece) {
                    ++$emptyCount;
                    continue;
                }

                if ($emptyCount > 0) {
                    $row .= $emptyCount;
                    $emptyCount = 0;
                }

                $row .= $piece->value;
            }

            if ($emptyCount > 0) {
                $row .= $emptyCount;
            }
            $rows[] = $row;
        }

        $fen = implode('/', $rows);

        $fen .= ' ' . $position->getSideToMove()->value;

        $castlingRights = $position->getCastlingRights();

        $fen .= ' ' . ($castlingRights ? implode('', array_map(fn (CastlingEnum $c) => $c->value, $castlingRights)) : '-');
        $fen .= ' ' . ($position->getEnPassantTarget()->value ?? '-');
        $fen .= ' ' . $position->getHalfmoveClock();
        $fen .= ' ' . $position->getFullmoveNumber();

        return $fen;
    }
}
