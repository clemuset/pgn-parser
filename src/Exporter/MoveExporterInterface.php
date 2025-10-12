<?php

namespace Cmuset\PgnParser\Exporter;

use Cmuset\PgnParser\Model\Move;

interface MoveExporterInterface
{
    public function export(Move $move): string;
}
