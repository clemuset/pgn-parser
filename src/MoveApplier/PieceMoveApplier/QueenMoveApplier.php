<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class QueenMoveApplier extends PieceMoveApplier
{
    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        $isQueenMove = MoveHelper::isStraightMove($from, $to) || MoveHelper::isSlidingMove($from, $to);

        return $isQueenMove && MoveHelper::isPathClear($from, $to, $position);
    }
}
