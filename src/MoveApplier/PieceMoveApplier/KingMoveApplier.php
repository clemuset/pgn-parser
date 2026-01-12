<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\Exception\MoveApplyingException;
use Cmuset\PgnParser\MoveApplier\MoveHelper;
use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;

class KingMoveApplier extends PieceMoveApplier
{
    public function apply(Position $position, Move $move): void
    {
        if ($move->isCastling()) {
            $this->applyCastling($position, $move);

            return;
        }

        parent::apply($position, $move);
    }

    private function applyCastling(Position $position, Move $move): void
    {
        $castling = $move->getCastling();

        $canCastling = $position->castlingIsAllowed($castling)
            && MoveHelper::isCastlingPathClear($position, $castling)
            && !MoveHelper::areCastlingSquaresAttacked($position, $castling);

        if (!$canCastling) {
            throw new MoveApplyingException(MoveViolationEnum::CASTLING_IS_NOT_ALLOWED);
        }

        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                $position->setPieceAt(CoordinatesEnum::G1, PieceEnum::WHITE_KING);
                $position->setPieceAt(CoordinatesEnum::F1, PieceEnum::WHITE_ROOK);
                $position->setPieceAt(CoordinatesEnum::E1, null);
                $position->setPieceAt(CoordinatesEnum::H1, null);
                break;
            case CastlingEnum::WHITE_QUEENSIDE:
                $position->setPieceAt(CoordinatesEnum::C1, PieceEnum::WHITE_KING);
                $position->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_ROOK);
                $position->setPieceAt(CoordinatesEnum::E1, null);
                $position->setPieceAt(CoordinatesEnum::A1, null);
                break;
            case CastlingEnum::BLACK_KINGSIDE:
                $position->setPieceAt(CoordinatesEnum::G8, PieceEnum::BLACK_KING);
                $position->setPieceAt(CoordinatesEnum::F8, PieceEnum::BLACK_ROOK);
                $position->setPieceAt(CoordinatesEnum::E8, null);
                $position->setPieceAt(CoordinatesEnum::H8, null);
                break;
            case CastlingEnum::BLACK_QUEENSIDE:
                $position->setPieceAt(CoordinatesEnum::C8, PieceEnum::BLACK_KING);
                $position->setPieceAt(CoordinatesEnum::D8, PieceEnum::BLACK_ROOK);
                $position->setPieceAt(CoordinatesEnum::E8, null);
                $position->setPieceAt(CoordinatesEnum::A8, null);
                break;
        }
    }

    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return MoveHelper::isKingMove($from, $to);
    }
}
