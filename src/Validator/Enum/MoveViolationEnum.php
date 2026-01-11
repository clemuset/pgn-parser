<?php

namespace Cmuset\PgnParser\Validator\Enum;

enum MoveViolationEnum: string
{
    case PIECE_NOT_FOUND = 'No piece found for the move';
    case MULTIPLE_PIECES_MATCH = 'Multiple pieces match the move piece';
    case NO_PIECE_TO_CAPTURE = 'No piece to capture on the target square';
    case CASTLING_IS_NOT_ALLOWED = 'Castling is not allowed in the current position';
    case WRONG_COLOR_TO_MOVE = 'It is not the correct color to move';
    case NEXT_POSITION_INVALID = 'The resulting position after the move is invalid';
    case MOVE_NOT_CHECKMATE = 'The move does not result in checkmate when expected';
    case MOVE_NOT_CHECK = 'The move does not result in check when expected';
    case SQUARE_OCCUPIED_BY_OWN_PIECE = 'The target square is occupied by own piece';
}
