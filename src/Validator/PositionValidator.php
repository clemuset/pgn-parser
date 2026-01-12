<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Validator\Enum\PositionViolationEnum;

class PositionValidator implements PositionValidatorInterface
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

        if (count($position->findAttackers($opponentKingSquare, $position->getSideToMove())) >= 1) {
            $violations[] = PositionViolationEnum::KING_IN_CHECK;
        }

        $whitePawns = $position->find(PieceEnum::WHITE_PAWN);
        $blackPawns = $position->find(PieceEnum::BLACK_PAWN);

        foreach ([...$whitePawns, ...$blackPawns] as $pawnSquare) {
            $rank = $pawnSquare->getCoordinates()->rank();

            if (1 === $rank || 8 === $rank) {
                $violations[] = PositionViolationEnum::PAWN_ON_INVALID_RANK;
                break;
            }
        }

        if (count($whitePawns) > 8 || count($blackPawns) > 8) {
            $violations[] = PositionViolationEnum::TOO_MANY_PAWNS;
        }

        if (null !== $position->getEnPassantTarget()) {
            $enPassantSquare = $position->getEnPassantTarget();
            $expectedRank = ColorEnum::WHITE === $position->getSideToMove() ? 6 : 3;

            if ($enPassantSquare->rank() !== $expectedRank || null !== $position->getPieceAt($enPassantSquare)) {
                $violations[] = PositionViolationEnum::EN_PASSANT_SQUARE_INVALID;
            }
        }

        return $violations;
    }
}
