<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;

class Square
{
    public function __construct(
        private readonly SquareEnum $square,
        private ?PieceEnum $piece = null
    ) {
    }

    public function getSquare(): SquareEnum
    {
        return $this->square;
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
