<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;

class Square
{
    public function __construct(
        private readonly CoordinatesEnum $coordinates,
        private ?PieceEnum $piece = null
    ) {
    }

    public function getCoordinates(): CoordinatesEnum
    {
        return $this->coordinates;
    }

    public function getPiece(): ?PieceEnum
    {
        return $this->piece;
    }

    public function setPiece(?PieceEnum $piece): void
    {
        $this->piece = $piece;
    }

    public function isEmpty(): bool
    {
        return null === $this->piece;
    }
}
