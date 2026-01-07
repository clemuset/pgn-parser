<?php

namespace Cmuset\PgnParser\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;

class MoveHelper
{
    public static function isPathClear(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        $fileStep = self::sign(ord($to->file()) - ord($from->file()));
        $rankStep = self::sign($to->rank() - $from->rank());

        $currentFileOrd = ord($from->file()) + $fileStep;
        $currentRank = $from->rank() + $rankStep;

        while ($currentFileOrd !== ord($to->file()) || $currentRank !== $to->rank()) {
            $squareName = chr($currentFileOrd) . $currentRank;
            $squareEnum = SquareEnum::tryFrom($squareName);

            if (!$squareEnum) {
                return false; // out of board
            }

            if (null !== $position->getPieceAt($squareEnum)) {
                return false; // piece blocking the path
            }

            $currentFileOrd += $fileStep;
            $currentRank += $rankStep;
        }

        return true;
    }

    public static function isSlidingMove(SquareEnum $from, SquareEnum $to): bool
    {
        $fileDiff = ord($to->file()) - ord($from->file());
        $rankDiff = $to->rank() - $from->rank();

        return $from !== $to && abs($fileDiff) === abs($rankDiff);
    }

    public static function isVerticalMove(SquareEnum $from, SquareEnum $to): bool
    {
        return $from->file() === $to->file() && $from->rank() !== $to->rank();
    }

    public static function isHorizontalMove(SquareEnum $from, SquareEnum $to): bool
    {
        return $from->rank() === $to->rank() && $from->file() !== $to->file();
    }

    public static function isStraightMove(SquareEnum $from, SquareEnum $to): bool
    {
        return self::isVerticalMove($from, $to) || self::isHorizontalMove($from, $to);
    }

    public static function isKnightMove(SquareEnum $from, SquareEnum $to): bool
    {
        $fileDiff = abs(ord($to->file()) - ord($from->file()));
        $rankDiff = abs($to->rank() - $from->rank());

        return (2 === $fileDiff && 1 === $rankDiff) || (1 === $fileDiff && 2 === $rankDiff);
    }

    public static function isPawnMove(SquareEnum $from, SquareEnum $to, ColorEnum $colorEnum): bool
    {
        $fileDiff = ord($to->file()) - ord($from->file());
        $rankDiff = $to->rank() - $from->rank();

        $direction = ColorEnum::WHITE === $colorEnum ? 1 : -1;

        // Normal move
        if (0 === $fileDiff && $rankDiff === $direction) {
            return true;
        }

        $pawnIsAtStartingRank = (ColorEnum::WHITE === $colorEnum && 2 === $from->rank())
            || (ColorEnum::BLACK === $colorEnum && 7 === $from->rank());

        // Initial double move
        if (0 === $fileDiff && $rankDiff === (2 * $direction) && $pawnIsAtStartingRank) {
            return true;
        }

        return false;
    }

    public static function isPawnCaptureMove(SquareEnum $from, SquareEnum $to, ColorEnum $colorEnum): bool
    {
        $fileDiff = ord($to->file()) - ord($from->file());
        $rankDiff = $to->rank() - $from->rank();

        $direction = ColorEnum::WHITE === $colorEnum ? 1 : -1;

        // Capture move
        if (1 === abs($fileDiff) && $rankDiff === $direction) {
            return true;
        }

        return false;
    }

    public static function isKingMove(SquareEnum $from, SquareEnum $to): bool
    {
        $fileDiff = abs(ord($to->file()) - ord($from->file()));
        $rankDiff = abs($to->rank() - $from->rank());

        return $fileDiff <= 1 && $rankDiff <= 1 && ($fileDiff + $rankDiff > 0);
    }

    public static function isCastlingPathClear(Position $position, CastlingEnum $castling): bool
    {
        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                return self::isPathClear(SquareEnum::E1, SquareEnum::G1, $position);
            case CastlingEnum::WHITE_QUEENSIDE:
                return self::isPathClear(SquareEnum::E1, SquareEnum::C1, $position);
            case CastlingEnum::BLACK_KINGSIDE:
                return self::isPathClear(SquareEnum::E8, SquareEnum::G8, $position);
            case CastlingEnum::BLACK_QUEENSIDE:
                return self::isPathClear(SquareEnum::E8, SquareEnum::C8, $position);
            default:
                return false;
        }
    }

    public static function areCastlingSquaresAttacked(Position $position, CastlingEnum $castling): bool
    {
        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                return $position->hasAttacker(SquareEnum::E1)
                    || $position->hasAttacker(SquareEnum::F1)
                    || $position->hasAttacker(SquareEnum::G1);
            case CastlingEnum::WHITE_QUEENSIDE:
                return $position->hasAttacker(SquareEnum::E1)
                    || $position->hasAttacker(SquareEnum::D1)
                    || $position->hasAttacker(SquareEnum::C1);
            case CastlingEnum::BLACK_KINGSIDE:
                return $position->hasAttacker(SquareEnum::E8)
                    || $position->hasAttacker(SquareEnum::F8)
                    || $position->hasAttacker(SquareEnum::G8);
            case CastlingEnum::BLACK_QUEENSIDE:
                return $position->hasAttacker(SquareEnum::E8)
                    || $position->hasAttacker(SquareEnum::D8)
                    || $position->hasAttacker(SquareEnum::C8);
            default:
                throw new \RuntimeException('Invalid castling enum');
        }
    }

    public static function getRookKingCastlingSquare(ColorEnum $color): SquareEnum
    {
        return ColorEnum::WHITE === $color ? SquareEnum::F1 : SquareEnum::F8;
    }

    public static function getRookQueenCastlingSquare(ColorEnum $color): SquareEnum
    {
        return ColorEnum::WHITE === $color ? SquareEnum::D1 : SquareEnum::D8;
    }

    private static function sign(int $value): int
    {
        if (0 === $value) {
            return 0;
        }

        return $value > 0 ? 1 : -1;
    }
}
