<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class PawnMoveApplier extends AbstractPieceMoveApplier
{
    public function apply(Position $position, Move $move): void
    {
        parent::apply($position, $move);

        if (null !== $move->getPromotion()) {
            $position->setPieceAt($move->getTo(), $move->getPromotion());
        }
    }

    public function canMove(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        return (MoveHelper::isPawnMove($from, $to, $position->getSideToMove())
            && MoveHelper::isPathClear($from, $to, $position))
            || $this->isAttacking($from, $to, $position);
    }

    public function isAttacking(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        return MoveHelper::isPawnCaptureMove($from, $to, $position->getSideToMove())
            && (null !== $position->getPieceAt($to) || $position->getEnPassantTarget() === $to);
    }
}
