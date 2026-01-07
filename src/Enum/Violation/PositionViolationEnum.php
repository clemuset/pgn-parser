<?php

namespace Cmuset\PgnParser\Enum\Violation;

enum PositionViolationEnum: string implements ViolationEnumInterface
{
    case KING_IN_CHECK = 'King in check';
    case NO_WHITE_KING = 'No white king present';
    case NO_BLACK_KING = 'No black king present';
    case MULTIPLE_WHITE_KINGS = 'Multiple white kings present';
    case MULTIPLE_BLACK_KINGS = 'Multiple black kings present';
    case PAWN_ON_INVALID_RANK = 'Pawn on invalid rank';
    case TOO_MANY_PAWNS = 'Too many pawns for one color';
    case EN_PASSANT_SQUARE_INVALID = 'En passant square is invalid';
}
