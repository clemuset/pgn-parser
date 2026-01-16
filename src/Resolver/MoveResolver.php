<?php

namespace Cmuset\PgnParser\Resolver;

use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\PieceMoveApplier;

class MoveResolver implements MoveResolverInterface
{
    public function resolve(Position $position, Move $unresolvedMove): Move
    {
        $resolvedMove = clone $unresolvedMove;

        $pieceToMove = $unresolvedMove->getPiece();
        $pieceMoveApplier = PieceMoveApplier::createFromPiece($pieceToMove);

        $fromSquare = $pieceMoveApplier->findWherePieceIs($position, $unresolvedMove);
        $resolvedMove->setSquareFrom($fromSquare);

        $isCapture = null !== $position->getPieceAt($unresolvedMove->getTo())
            || ($pieceToMove->isPawn() && $position->getEnPassantTarget() === $unresolvedMove->getTo());
        $resolvedMove->setIsCapture($isCapture);

        ($nextPos = clone $position)->applyMove($resolvedMove);
        $resolvedMove->setIsCheckmate($nextPos->isCheckmate());

        if (!$resolvedMove->isCheckmate()) {
            $resolvedMove->setIsCheck($nextPos->isCheck());
        }

        return $resolvedMove;
    }
}
