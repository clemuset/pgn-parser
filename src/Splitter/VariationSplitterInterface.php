<?php

namespace Cmuset\PgnParser\Splitter;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Variation;

interface VariationSplitterInterface
{
    /**
     * @return Variation[]
     */
    public function split(Game|Variation $variation, ?SplitOptions $options = null): array;
}
