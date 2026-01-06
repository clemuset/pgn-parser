<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\Violation\PositionViolationEnum;
use Cmuset\PgnParser\Model\Position;

class PositionValidator
{
    /**
     * @return PositionViolationEnum[]
     */
    public function validate(Position $position): array
    {
        $violations = [];

        $whiteKings = $position->find(PieceEnum::WHITE_KING);
        $whiteKingCount = count($whiteKings);
        $blackKings = $position->find(PieceEnum::BLACK_KING);
        $blackKingCount = count($blackKings);

        if (0 === $whiteKingCount) {
            $violations[] = PositionViolationEnum::NO_WHITE_KING;
        }

        if ($whiteKingCount > 1) {
            $violations[] = PositionViolationEnum::MULTIPLE_WHITE_KINGS;
        }

        if (0 === $blackKingCount) {
            $violations[] = PositionViolationEnum::NO_BLACK_KING;
        }

        if ($blackKingCount > 1) {
            $violations[] = PositionViolationEnum::MULTIPLE_BLACK_KINGS;
        }

        if (count($violations) > 0) {
            return $violations;
        }

        $opponentKingSquare = ColorEnum::BLACK === $position->getSideToMove()
            ? $position->findOne(PieceEnum::WHITE_KING)
            : $position->findOne(PieceEnum::BLACK_KING);

        if (count($position->findAttackers($opponentKingSquare)) >= 1) {
            $violations[] = PositionViolationEnum::KING_IN_CHECK;
        }

        return $violations;
    }
}
