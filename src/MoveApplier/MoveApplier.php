<?php

namespace Cmuset\PgnParser\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\Exception\MoveApplyingException;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\PieceMoveApplier;
use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;
use Cmuset\PgnParser\Validator\PositionValidator;

class MoveApplier implements MoveApplierInterface
{
    public static function create(): self
    {
        return new self();
    }

    public function apply(Position $position, Move $move): void
    {
        $pieceToMove = $move->getPiece();

        $pieceMoveApplier = PieceMoveApplier::createFromPiece($pieceToMove);

        $this->throwMoveViolationException($position, $move);

        $pieceMoveApplier->apply($position, $move);

        $this->handleCastlingRights($position, $move);

        if ($this->isCapture($position, $move) || $pieceToMove->isPawn()) {
            $position->setHalfmoveClock(0);
        } else {
            $position->setHalfmoveClock($position->getHalfmoveClock() + 1);
        }

        if (ColorEnum::BLACK === $pieceToMove->color()) {
            $position->setFullmoveNumber($position->getFullmoveNumber() + 1);
        }

        $position->toggleSideToMove();

        if ($move->isCheck() && !$position->isCheck()) {
            throw new MoveApplyingException(MoveViolationEnum::MOVE_NOT_CHECK);
        }

        if ($move->isCheckmate() && !$position->isCheckmate()) {
            throw new MoveApplyingException(MoveViolationEnum::MOVE_NOT_CHECKMATE);
        }

        $positionViolations = new PositionValidator()->validate($position);

        if (count($positionViolations) > 0) {
            throw new MoveApplyingException(MoveViolationEnum::NEXT_POSITION_INVALID, $positionViolations);
        }
    }

    private function handleCastlingRights(Position $position, Move $move): void
    {
        if (null === $position->getPieceAt(CoordinatesEnum::E8) || CoordinatesEnum::E8 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::BLACK_KINGSIDE);
            $position->removeCastlingRight(CastlingEnum::BLACK_QUEENSIDE);
        }

        if (null === $position->getPieceAt(CoordinatesEnum::E1) || CoordinatesEnum::E1 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::WHITE_KINGSIDE);
            $position->removeCastlingRight(CastlingEnum::WHITE_QUEENSIDE);
        }

        if (null === $position->getPieceAt(CoordinatesEnum::A1) || CoordinatesEnum::A1 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::WHITE_QUEENSIDE);
        }

        if (null === $position->getPieceAt(CoordinatesEnum::H1) || CoordinatesEnum::H1 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::WHITE_KINGSIDE);
        }

        if (null === $position->getPieceAt(CoordinatesEnum::A8) || CoordinatesEnum::A8 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::BLACK_QUEENSIDE);
        }

        if (null === $position->getPieceAt(CoordinatesEnum::H8) || CoordinatesEnum::H8 === $move->getTo()) {
            $position->removeCastlingRight(CastlingEnum::BLACK_KINGSIDE);
        }
    }

    private function isCapture(Position $position, Move $move): bool
    {
        if (null === $move->getTo()) {
            return false;
        }

        return null !== $position->getPieceAt($move->getTo())
            || ($move->getPiece()->isPawn() && $move->getTo() === $position->getEnPassantTarget());
    }

    private function throwMoveViolationException(Position $position, Move $move): void
    {
        $pieceToMove = $move->getPiece();

        if (!$move->isCastling() && $position->getSideToMove() === $position->getPieceAt($move->getTo())?->color()) {
            throw new MoveApplyingException(MoveViolationEnum::SQUARE_OCCUPIED_BY_OWN_PIECE);
        }

        if ($pieceToMove->color() !== $position->getSideToMove()) {
            throw new MoveApplyingException(MoveViolationEnum::WRONG_COLOR_TO_MOVE);
        }

        if (false === $this->isCapture($position, $move) && $move->isCapture()) {
            throw new MoveApplyingException(MoveViolationEnum::NO_PIECE_TO_CAPTURE);
        }
    }
}
