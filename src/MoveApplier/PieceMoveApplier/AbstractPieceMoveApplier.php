<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Square;
use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;

abstract class AbstractPieceMoveApplier
{
    public function apply(Position $position, Move $move): void
    {
        $potentialSquares = [];
        foreach ($this->findSquaresWherePieceIs($position, $move) as $square) {
            if ($this->canMove($square, $move->getTo(), $position)) {
                $potentialSquares[] = $square;
            }
        }

        if (count($potentialSquares) > 1) {
            throw new MoveApplyingException(MoveViolationEnum::MULTIPLE_PIECES_MATCH);
        }

        if (0 === count($potentialSquares)) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        $fromSquare = $potentialSquares[0];

        $position->setPieceAt($fromSquare, null);
        $position->setPieceAt($move->getTo(), $move->getPiece());

        if ($move->getPiece()->isPawn() && 2 === abs($fromSquare->rank() - $move->getTo()->rank())) {
            $enPassantRank = ($fromSquare->rank() + $move->getTo()->rank()) / 2;
            $position->setEnPassantTarget(CoordinatesEnum::tryFrom($fromSquare->file() . $enPassantRank));
        } else {
            $position->setEnPassantTarget(null);
        }
    }

    public function canMove(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        // For non-pawn pieces, movement and attacking squares are the same
        return $this->isAttacking($from, $to, $position);
    }

    abstract public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool;

    private function findSquaresWherePieceIs(Position $position, Move $move): array
    {
        $pieceToMove = $move->getPiece();

        if (null !== $move->getSquareFrom()) {
            return $pieceToMove === $position->getPieceAt($move->getSquareFrom()) ? [$move->getSquareFrom()] : [];
        }

        if (null !== $move->getFileFrom()) {
            return array_map(
                fn (Square $square) => $square->getCoordinates(),
                $position->findByFile($pieceToMove, $move->getFileFrom())
            );
        }

        if (null !== $move->getRankFrom()) {
            return array_map(
                fn (Square $square) => $square->getCoordinates(),
                $position->findByRank($pieceToMove, $move->getRankFrom())
            );
        }

        return array_map(
            fn (Square $square) => $square->getCoordinates(),
            $position->find($pieceToMove)
        );
    }
}
