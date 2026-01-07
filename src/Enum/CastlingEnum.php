<?php

namespace Cmuset\PgnParser\Enum;

enum CastlingEnum: string
{
    case WHITE_KINGSIDE = 'K';
    case WHITE_QUEENSIDE = 'Q';
    case BLACK_KINGSIDE = 'k';
    case BLACK_QUEENSIDE = 'q';

    public static function kingside(ColorEnum $color): CastlingEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_KINGSIDE : self::WHITE_KINGSIDE;
    }

    public static function queenside(ColorEnum $color): CastlingEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_QUEENSIDE : self::WHITE_QUEENSIDE;
    }

    public function color(): ColorEnum
    {
        return match ($this) {
            self::WHITE_KINGSIDE, self::WHITE_QUEENSIDE => ColorEnum::WHITE,
            self::BLACK_KINGSIDE, self::BLACK_QUEENSIDE => ColorEnum::BLACK,
        };
    }
}
