<?php

namespace Cmuset\PgnParser\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveHelper;

class KnightMoveApplier extends AbstractPieceMoveApplier
{
    public function isAttacking(SquareEnum $from, SquareEnum $to, Position $position): bool
    {
        return MoveHelper::isKnightMove($from, $to);
    }
}
