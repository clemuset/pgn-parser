<?php

namespace Cmuset\PgnParser\Parser;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Model\Move;

interface SANParserInterface
{
    public function parse(string $san, ColorEnum $color): Move;
}
