<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Enum\Violation\MoveViolationEnum;
use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class KingMoveApplier extends AbstractPieceMoveApplier
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
                $position->setPieceAt(SquareEnum::G1, PieceEnum::WHITE_KING);
                $position->setPieceAt(SquareEnum::F1, PieceEnum::WHITE_ROOK);
                $position->setPieceAt(SquareEnum::E1, null);
                $position->setPieceAt(SquareEnum::H1, null);
                break;
            case CastlingEnum::WHITE_QUEENSIDE:
                $position->setPieceAt(SquareEnum::C1, PieceEnum::WHITE_KING);
                $position->setPieceAt(SquareEnum::D1, PieceEnum::WHITE_ROOK);
                $position->setPieceAt(SquareEnum::E1, null);
                $position->setPieceAt(SquareEnum::A1, null);
                break;
            case CastlingEnum::BLACK_KINGSIDE:
                $position->setPieceAt(SquareEnum::G8, PieceEnum::BLACK_KING);
                $position->setPieceAt(SquareEnum::F8, PieceEnum::BLACK_ROOK);
                $position->setPieceAt(SquareEnum::E8, null);
                $position->setPieceAt(SquareEnum::H8, null);
                break;
            case CastlingEnum::BLACK_QUEENSIDE:
                $position->setPieceAt(SquareEnum::C8, PieceEnum::BLACK_KING);
                $position->setPieceAt(SquareEnum::D8, PieceEnum::BLACK_ROOK);
                $position->setPieceAt(SquareEnum::E8, null);
                $position->setPieceAt(SquareEnum::A8, null);
                break;
        }
    }

    public function isAttacking(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        return MoveHelper::isKingMove($from, $to);
    }
}
