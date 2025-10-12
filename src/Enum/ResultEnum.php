<?php

namespace Cmuset\PgnParser\Enum;

enum ResultEnum: string
{
    case WHITE_WIN = '1-0';
    case BLACK_WIN = '0-1';
    case DRAW = '1/2-1/2';
    case ONGOING = '*';
}
