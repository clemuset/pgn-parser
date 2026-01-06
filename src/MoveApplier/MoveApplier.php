<?php

namespace Cmuset\PgnParser\MoveApplier;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Enum\Violation\MoveViolationEnum;
use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Validator\PositionValidator;

class MoveApplier
{
    public function __construct(
        private readonly bool $verifyCheckmate = true
    ) {
    }

    public function apply(Position $previousPosition, Move $move): Position
    {
        $position = clone $previousPosition;

        $toSquare = $move->getTo();
        $pieceToMove = $move->getPiece();

        if ($pieceToMove->color() !== $position->getSideToMove()) {
            throw new MoveApplyingException(MoveViolationEnum::WRONG_COLOR_TO_MOVE);
        }

        $position->setHalfmoveClock($position->getHalfmoveClock() + 1);

        if (ColorEnum::BLACK === $pieceToMove->color()) {
            $position->setFullmoveNumber($position->getFullmoveNumber() + 1);
        }

        if ($move->isCastling()) {
            $castling = $move->getCastling();

            if (!$position->castlingIsAllowed($castling)) {
                throw new MoveApplyingException(MoveViolationEnum::CASTLING_IS_NOT_ALLOWED);
            }

            $this->applyCastling($position, $castling);

            return $position;
        }

        $isCapture = null !== $position->getPieceAt($toSquare);

        if (false === $isCapture && $move->isCapture()) {
            throw new MoveApplyingException(MoveViolationEnum::NO_PIECE_TO_CAPTURE);
        }

        $potentialAttackers = $this->findPotentialAttackers($position, $move);

        if (count($potentialAttackers) > 1) {
            throw new MoveApplyingException(MoveViolationEnum::MULTIPLE_PIECES_MATCH);
        }

        if (0 === count($potentialAttackers)) {
            if (!$pieceToMove->isPawn()) {
                throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
            }

            $this->applyPawnMove($position, $move);

            return $position;
        }

        $attacker = $potentialAttackers[0];
        $position->setPieceAt($toSquare, $pieceToMove);
        $position->setPieceAt($attacker->getSquare(), null);
        $position->toggleSideToMove();
        $this->handlePromotion($position, $move);

        $this->handleCastlingRights($pieceToMove, $attacker->getSquare(), $position);

        if ($isCapture) {
            $position->setHalfmoveClock(0);
        }

        $positionViolations = new PositionValidator()->validate($position);

        if (count($positionViolations) > 0) {
            throw new MoveApplyingException(MoveViolationEnum::NEXT_POSITION_INVALID, $positionViolations);
        }

        if ($move->isCheck() && !$position->isCheck()) {
            throw new MoveApplyingException(MoveViolationEnum::MOVE_NOT_CHECK);
        }

        if ($this->verifyCheckmate && $move->isCheckmate() && !$position->isCheckmate()) {
            throw new MoveApplyingException(MoveViolationEnum::MOVE_NOT_CHECKMATE);
        }

        return $position;
    }

    private function applyCastling(Position $position, CastlingEnum $castling): Position
    {
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

        $position->toggleSideToMove();

        return $position;
    }

    private function applyPawnMove(Position $position, Move $move): void
    {
        $pawn = $move->getPiece();
        $toSquare = $move->getTo();
        $fromHint = $move->getSquareFrom();

        $squareFrom = $position->findPawnMovableAt($toSquare);

        if (null === $squareFrom) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        if (null !== $fromHint && $squareFrom->getSquare() !== $fromHint) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        if (null !== $move->getFileFrom() && $squareFrom->getSquare()->file() !== $move->getFileFrom()) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        if (null !== $move->getRowFrom() && $squareFrom->getSquare()->rank() !== $move->getRowFrom()) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        $position->setPieceAt($toSquare, $pawn);
        $position->setPieceAt($squareFrom->getSquare(), null);
        $position->toggleSideToMove();
        $position->setHalfmoveClock(0);
        $this->handlePromotion($position, $move);
    }

    private function handlePromotion(Position $position, Move $move): void
    {
        $toSquare = $move->getTo();
        $promotionPiece = $move->getPromotion();

        if (null === $promotionPiece) {
            return;
        }

        $color = $move->getPiece()->color();

        if (!in_array($promotionPiece, [PieceEnum::rook($color), PieceEnum::knight($color), PieceEnum::bishop($color), PieceEnum::queen($color)])) {
            throw new MoveApplyingException(MoveViolationEnum::INVALID_PROMOTION_PIECE);
        }

        if (ColorEnum::BLACK === $color && 1 !== $toSquare->rank()) {
            throw new MoveApplyingException(MoveViolationEnum::INVALID_PROMOTION_SQUARE);
        }

        if (ColorEnum::WHITE === $color && 8 !== $toSquare->rank()) {
            throw new MoveApplyingException(MoveViolationEnum::INVALID_PROMOTION_SQUARE);
        }

        $position->setPieceAt($toSquare, $promotionPiece);
    }

    private function findPotentialAttackers(Position $position, Move $move): array
    {
        $pieceToMove = $move->getPiece();
        $toSquare = $move->getTo();

        $fromSquare = $move->getSquareFrom();
        $fileFrom = $move->getFileFrom();
        $rowFrom = $move->getRowFrom();

        $attackers = $position->findAttackers($toSquare);

        $potentialAttackers = [];
        foreach ($attackers as $attacker) {
            if ($attacker->getSquare() === $fromSquare && $attacker->getPiece() === $pieceToMove) {
                return [$attacker];
            }

            if ($attacker->getSquare() === $fromSquare && $attacker->getPiece() !== $pieceToMove) {
                throw new MoveApplyingException(MoveViolationEnum::PIECE_ON_FROM_SQUARE_MISMATCH);
            }

            if (null !== $fromSquare && $attacker->getSquare() !== $fromSquare) {
                continue;
            }

            if (null !== $fileFrom && $attacker->getSquare()->file() !== $fileFrom) {
                continue;
            }

            if (null !== $rowFrom && $attacker->getSquare()->rank() !== $rowFrom) {
                continue;
            }

            if ($attacker->getPiece() !== $pieceToMove) {
                continue;
            }

            $potentialAttackers[] = $attacker;
        }

        return $potentialAttackers;
    }

    private function handleCastlingRights(PieceEnum $pieceToMove, SquareEnum $squareFrom, Position $position): void
    {
        if (PieceEnum::BLACK_KING === $pieceToMove) {
            $this->removeCastlingRight($position, CastlingEnum::BLACK_KINGSIDE);
            $this->removeCastlingRight($position, CastlingEnum::BLACK_QUEENSIDE);
        }

        if (PieceEnum::WHITE_KING === $pieceToMove) {
            $this->removeCastlingRight($position, CastlingEnum::WHITE_KINGSIDE);
            $this->removeCastlingRight($position, CastlingEnum::WHITE_QUEENSIDE);
        }

        if (PieceEnum::BLACK_ROOK === $pieceToMove) {
            if (SquareEnum::H8 === $squareFrom) {
                $this->removeCastlingRight($position, CastlingEnum::BLACK_KINGSIDE);
            }

            if (SquareEnum::A8 === $squareFrom) {
                $this->removeCastlingRight($position, CastlingEnum::BLACK_QUEENSIDE);
            }
        }

        if (PieceEnum::WHITE_ROOK === $pieceToMove) {
            if (SquareEnum::H1 === $squareFrom) {
                $this->removeCastlingRight($position, CastlingEnum::WHITE_KINGSIDE);
            }

            if (SquareEnum::A1 === $squareFrom) {
                $this->removeCastlingRight($position, CastlingEnum::WHITE_QUEENSIDE);
            }
        }
    }

    private function removeCastlingRight(Position $position, CastlingEnum $castlingEnum): void
    {
        if ($position->hasCastlingRight($castlingEnum)) {
            $position->removeCastlingRight($castlingEnum);
        }
    }
}
