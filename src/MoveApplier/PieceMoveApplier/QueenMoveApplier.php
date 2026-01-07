<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class QueenMoveApplier extends AbstractPieceMoveApplier
{
    public function isAttacking(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        $isQueenMove = MoveHelper::isStraightMove($from, $to) || MoveHelper::isSlidingMove($from, $to);

        return $isQueenMove && MoveHelper::isPathClear($from, $to, $position);
    }
}
