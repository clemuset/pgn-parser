<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class BishopMoveApplier extends AbstractPieceMoveApplier
{
    public function isAttacking(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        return MoveHelper::isSlidingMove($from, $to) && MoveHelper::isPathClear($from, $to, $position);
    }
}
