<?php

namespace Cmuset\PgnParser\Splitter;

use Cmuset\PgnParser\Enum\ColorEnum;

class SplitOptions
{
    public function __construct(
        public readonly bool $keepPreviousMoves = false,
        public readonly ?ColorEnum $colorToSplit = null,
    ) {
    }
}
