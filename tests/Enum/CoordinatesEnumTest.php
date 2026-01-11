<?php

namespace Cmuset\PgnParser\Tests\Enum;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use PHPUnit\Framework\TestCase;

class CoordinatesEnumTest extends TestCase
{
    public function testFileExtraction(): void
    {
        self::assertSame('a', CoordinatesEnum::A1->file());
        self::assertSame('h', CoordinatesEnum::H8->file());
    }

    public function testRankExtraction(): void
    {
        self::assertSame(1, CoordinatesEnum::A1->rank());
        self::assertSame(8, CoordinatesEnum::H8->rank());
    }

    public function testColorLightSquares(): void
    {
        self::assertSame(ColorEnum::WHITE, CoordinatesEnum::A1->color());
        self::assertSame(ColorEnum::WHITE, CoordinatesEnum::H8->color());
    }

    public function testColorDarkSquares(): void
    {
        self::assertSame(ColorEnum::BLACK, CoordinatesEnum::A2->color());
        self::assertSame(ColorEnum::BLACK, CoordinatesEnum::B1->color());
    }

    public function testUpMovement(): void
    {
        self::assertSame(CoordinatesEnum::A2, CoordinatesEnum::A1->up());
        self::assertNull(CoordinatesEnum::A8->up());
    }

    public function testDownMovement(): void
    {
        self::assertSame(CoordinatesEnum::A7, CoordinatesEnum::A8->down());
        self::assertNull(CoordinatesEnum::A1->down());
    }

    public function testLeftMovement(): void
    {
        self::assertSame(CoordinatesEnum::A1, CoordinatesEnum::B1->left());
        self::assertNull(CoordinatesEnum::A1->left());
    }

    public function testRightMovement(): void
    {
        self::assertSame(CoordinatesEnum::B1, CoordinatesEnum::A1->right());
        self::assertNull(CoordinatesEnum::H1->right());
    }
}
