<?php

namespace Cmuset\PgnParser\Resolver;

use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;

interface MoveResolverInterface
{
    public function resolve(Position $position, Move $unresolvedMove): Move;
}
