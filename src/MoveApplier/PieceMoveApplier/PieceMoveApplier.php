<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Square;
use Cmuset\PgnParser\MoveApplier\Exception\MoveApplyingException;
use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;

abstract class PieceMoveApplier
{
    public static function createFromPiece(PieceEnum $piece): PieceMoveApplier
    {
        return match ($piece) {
            PieceEnum::WHITE_KING, PieceEnum::BLACK_KING => new KingMoveApplier(),
            PieceEnum::WHITE_QUEEN, PieceEnum::BLACK_QUEEN => new QueenMoveApplier(),
            PieceEnum::WHITE_ROOK, PieceEnum::BLACK_ROOK => new RookMoveApplier(),
            PieceEnum::WHITE_BISHOP, PieceEnum::BLACK_BISHOP => new BishopMoveApplier(),
            PieceEnum::WHITE_KNIGHT, PieceEnum::BLACK_KNIGHT => new KnightMoveApplier(),
            default => new PawnMoveApplier(),
        };
    }

    /**
     * @throws MoveApplyingException
     */
    public function apply(Position $position, Move $move): void
    {
        $fromSquare = $this->findWherePieceIs($position, $move);

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

    /**
     * @throws MoveApplyingException
     */
    final public function findWherePieceIs(Position $position, Move $move): CoordinatesEnum
    {
        $pieceToMove = $move->getPiece();
        $squareFrom = $move->getSquareFrom();

        $potentialCoordinates = match (true) {
            null !== $move->getSquareFrom() => $pieceToMove === $position->getPieceAt($squareFrom) ? [$squareFrom] : [],
            null !== $move->getFileFrom() => array_map(
                fn (Square $square) => $square->getCoordinates(),
                $position->findByFile($pieceToMove, $move->getFileFrom())
            ),
            null !== $move->getRankFrom() => array_map(
                fn (Square $square) => $square->getCoordinates(),
                $position->findByRank($pieceToMove, $move->getRankFrom())
            ),
            default => array_map(
                fn (Square $square) => $square->getCoordinates(),
                $position->find($pieceToMove)
            ),
        };

        $coordinates = [];
        foreach ($potentialCoordinates as $c) {
            if ($this->canMove($c, $move->getTo(), $position)) {
                $coordinates[] = $c;
            }
        }

        if (count($coordinates) > 1) {
            throw new MoveApplyingException(MoveViolationEnum::MULTIPLE_PIECES_MATCH);
        }

        if (0 === count($coordinates)) {
            throw new MoveApplyingException(MoveViolationEnum::PIECE_NOT_FOUND);
        }

        return $coordinates[0];
    }
}
