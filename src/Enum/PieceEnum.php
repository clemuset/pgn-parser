<?php

namespace Cmuset\PgnParser\Enum;

enum PieceEnum: string
{
    case WHITE_KING = 'K';
    case WHITE_QUEEN = 'Q';
    case WHITE_ROOK = 'R';
    case WHITE_BISHOP = 'B';
    case WHITE_KNIGHT = 'N';
    case WHITE_PAWN = 'P';
    case BLACK_KING = 'k';
    case BLACK_QUEEN = 'q';
    case BLACK_ROOK = 'r';
    case BLACK_BISHOP = 'b';
    case BLACK_KNIGHT = 'n';
    case BLACK_PAWN = 'p';

    public static function king(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_KING : self::WHITE_KING;
    }

    public static function queen(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_QUEEN : self::WHITE_QUEEN;
    }

    public static function rook(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_ROOK : self::WHITE_ROOK;
    }

    public static function bishop(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_BISHOP : self::WHITE_BISHOP;
    }

    public static function knight(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_KNIGHT : self::WHITE_KNIGHT;
    }

    public static function pawn(?ColorEnum $color = null): PieceEnum
    {
        return ColorEnum::BLACK === $color ? self::BLACK_PAWN : self::WHITE_PAWN;
    }

    public function color(): ColorEnum
    {
        return strtolower($this->value) === $this->value ? ColorEnum::BLACK : ColorEnum::WHITE;
    }

    public function isPawn(): bool
    {
        return in_array($this, [self::WHITE_PAWN, self::BLACK_PAWN], true);
    }

    public function opposite(): self
    {
        return match ($this) {
            self::WHITE_KING => self::BLACK_KING,
            self::WHITE_QUEEN => self::BLACK_QUEEN,
            self::WHITE_ROOK => self::BLACK_ROOK,
            self::WHITE_BISHOP => self::BLACK_BISHOP,
            self::WHITE_KNIGHT => self::BLACK_KNIGHT,
            self::WHITE_PAWN => self::BLACK_PAWN,
            self::BLACK_KING => self::WHITE_KING,
            self::BLACK_QUEEN => self::WHITE_QUEEN,
            self::BLACK_ROOK => self::WHITE_ROOK,
            self::BLACK_BISHOP => self::WHITE_BISHOP,
            self::BLACK_KNIGHT => self::WHITE_KNIGHT,
            self::BLACK_PAWN => self::WHITE_PAWN,
        };
    }
}
