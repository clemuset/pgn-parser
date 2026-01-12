<?php

namespace Cmuset\PgnParser\MoveApplier;

use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;

interface MoveApplierInterface
{
    public function apply(Position $position, Move $move): void;
}
