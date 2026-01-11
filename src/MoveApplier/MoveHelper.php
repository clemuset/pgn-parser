<?php

namespace Cmuset\PgnParser\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Model\Position;

class MoveHelper
{
    public static function isPathClear(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        $fileStep = self::sign(ord($to->file()) - ord($from->file()));
        $rankStep = self::sign($to->rank() - $from->rank());

        $currentFileOrd = ord($from->file()) + $fileStep;
        $currentRank = $from->rank() + $rankStep;

        while ($currentFileOrd !== ord($to->file()) || $currentRank !== $to->rank()) {
            $coordinatesStr = chr($currentFileOrd) . $currentRank;
            $coordinates = CoordinatesEnum::tryFrom($coordinatesStr);

            // Out of board || Piece blocking the path
            if (!$coordinates || null !== $position->getPieceAt($coordinates)) {
                return false;
            }

            $currentFileOrd += $fileStep;
            $currentRank += $rankStep;
        }

        return true;
    }

    public static function isSlidingMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        $fileDiff = ord($to->file()) - ord($from->file());
        $rankDiff = $to->rank() - $from->rank();

        return $from !== $to && abs($fileDiff) === abs($rankDiff);
    }

    public static function isVerticalMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        return $from->file() === $to->file() && $from->rank() !== $to->rank();
    }

    public static function isHorizontalMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        return $from->rank() === $to->rank() && $from->file() !== $to->file();
    }

    public static function isStraightMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        return self::isVerticalMove($from, $to) || self::isHorizontalMove($from, $to);
    }

    public static function isKnightMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        $fileDiff = abs(ord($to->file()) - ord($from->file()));
        $rankDiff = abs($to->rank() - $from->rank());

        return (2 === $fileDiff && 1 === $rankDiff) || (1 === $fileDiff && 2 === $rankDiff);
    }

    public static function isPawnMove(CoordinatesEnum $from, CoordinatesEnum $to, ColorEnum $colorEnum): bool
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

    public static function isPawnCaptureMove(CoordinatesEnum $from, CoordinatesEnum $to, ColorEnum $colorEnum): bool
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

    public static function isKingMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        $fileDiff = abs(ord($to->file()) - ord($from->file()));
        $rankDiff = abs($to->rank() - $from->rank());

        return $fileDiff <= 1 && $rankDiff <= 1 && ($fileDiff + $rankDiff > 0);
    }

    public static function isCastlingPathClear(Position $position, CastlingEnum $castling): bool
    {
        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                return self::isPathClear(CoordinatesEnum::E1, CoordinatesEnum::G1, $position);
            case CastlingEnum::WHITE_QUEENSIDE:
                return self::isPathClear(CoordinatesEnum::E1, CoordinatesEnum::C1, $position);
            case CastlingEnum::BLACK_KINGSIDE:
                return self::isPathClear(CoordinatesEnum::E8, CoordinatesEnum::G8, $position);
            case CastlingEnum::BLACK_QUEENSIDE:
                return self::isPathClear(CoordinatesEnum::E8, CoordinatesEnum::C8, $position);
            default:
                return false;
        }
    }

    public static function areCastlingSquaresAttacked(Position $position, CastlingEnum $castling): bool
    {
        $attackerColor = $castling->color()->opposite();

        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                return $position->hasAttacker(CoordinatesEnum::E1, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::F1, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::G1, $attackerColor);
            case CastlingEnum::WHITE_QUEENSIDE:
                return $position->hasAttacker(CoordinatesEnum::E1, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::D1, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::C1, $attackerColor);
            case CastlingEnum::BLACK_KINGSIDE:
                return $position->hasAttacker(CoordinatesEnum::E8, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::F8, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::G8, $attackerColor);
            case CastlingEnum::BLACK_QUEENSIDE:
                return $position->hasAttacker(CoordinatesEnum::E8, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::D8, $attackerColor)
                    || $position->hasAttacker(CoordinatesEnum::C8, $attackerColor);
            default:
                throw new \RuntimeException('Invalid castling enum');
        }
    }

    private static function sign(int $value): int
    {
        return 0 === $value
        ? 0
        : ($value > 0 ? 1 : -1);
    }
}
