<?php

namespace Cmuset\PgnParser\Enum;

enum ColorEnum: string
{
    case WHITE = 'w';
    case BLACK = 'b';

    public function opposite(): ColorEnum
    {
        return self::WHITE === $this ? self::BLACK : self::WHITE;
    }
}
