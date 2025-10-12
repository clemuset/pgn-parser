<?php

namespace Cmuset\PgnParser\Enum;

enum SquareEnum: string
{
    case A1 = 'a1';
    case A2 = 'a2';
    case A3 = 'a3';
    case A4 = 'a4';
    case A5 = 'a5';
    case A6 = 'a6';
    case A7 = 'a7';
    case A8 = 'a8';
    case B1 = 'b1';
    case B2 = 'b2';
    case B3 = 'b3';
    case B4 = 'b4';
    case B5 = 'b5';
    case B6 = 'b6';
    case B7 = 'b7';
    case B8 = 'b8';
    case C1 = 'c1';
    case C2 = 'c2';
    case C3 = 'c3';
    case C4 = 'c4';
    case C5 = 'c5';
    case C6 = 'c6';
    case C7 = 'c7';
    case C8 = 'c8';
    case D1 = 'd1';
    case D2 = 'd2';
    case D3 = 'd3';
    case D4 = 'd4';
    case D5 = 'd5';
    case D6 = 'd6';
    case D7 = 'd7';
    case D8 = 'd8';
    case E1 = 'e1';
    case E2 = 'e2';
    case E3 = 'e3';
    case E4 = 'e4';
    case E5 = 'e5';
    case E6 = 'e6';
    case E7 = 'e7';
    case E8 = 'e8';
    case F1 = 'f1';
    case F2 = 'f2';
    case F3 = 'f3';
    case F4 = 'f4';
    case F5 = 'f5';
    case F6 = 'f6';
    case F7 = 'f7';
    case F8 = 'f8';
    case G1 = 'g1';
    case G2 = 'g2';
    case G3 = 'g3';
    case G4 = 'g4';
    case G5 = 'g5';
    case G6 = 'g6';
    case G7 = 'g7';
    case G8 = 'g8';
    case H1 = 'h1';
    case H2 = 'h2';
    case H3 = 'h3';
    case H4 = 'h4';
    case H5 = 'h5';
    case H6 = 'h6';
    case H7 = 'h7';
    case H8 = 'h8';

    public function file(): string
    {
        return $this->value[0];
    }

    public function rank(): int
    {
        return (int) $this->value[1];
    }

    public function color(): ColorEnum
    {
        $file = ord($this->file()) - ord('a') + 1;
        $rank = $this->rank();

        return (($file + $rank) % 2 === 0) ? ColorEnum::WHITE : ColorEnum::BLACK;
    }
}
