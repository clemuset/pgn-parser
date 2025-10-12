<?php

namespace Cmuset\PgnParser\Parser;

use Cmuset\PgnParser\Model\Position;

interface FENParserInterface
{
    public function parse(string $fen): Position;
}
