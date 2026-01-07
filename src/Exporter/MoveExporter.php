<?php

namespace Cmuset\PgnParser\Exporter;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Model\Move;

class MoveExporter implements MoveExporterInterface
{
    public function export(Move $move): string
    {
        $san = $move->getPiece()->isPawn() ? '' : strtoupper($move->getPiece()->value);
        $san .= $move->getFileFrom() . $move->getRankFrom();

        if ($move->isCastling()) {
            return match ($move->getCastling()) {
                CastlingEnum::BLACK_KINGSIDE, CastlingEnum::WHITE_KINGSIDE => 'O-O',
                CastlingEnum::BLACK_QUEENSIDE, CastlingEnum::WHITE_QUEENSIDE => 'O-O-O',
                default => throw new \InvalidArgumentException('Invalid castling move'),
            };
        }

        if ($move->isCapture()) {
            $san .= 'x';
        }

        $san .= $move->getTo()?->value;

        if ($promo = $move->getPromotion()) {
            $san .= '=' . strtoupper($promo->value);
        }

        if ($move->isCheckmate()) {
            $san .= '#';
        } elseif ($move->isCheck()) {
            $san .= '+';
        }

        return $san;
    }
}
