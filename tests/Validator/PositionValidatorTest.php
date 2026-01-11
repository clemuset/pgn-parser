<?php

namespace Cmuset\PgnParser\Tests\Validator;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Parser\PGNParser;
use Cmuset\PgnParser\Validator\Enum\PositionViolationEnum;
use Cmuset\PgnParser\Validator\PositionValidator;
use PHPUnit\Framework\TestCase;

class PositionValidatorTest extends TestCase
{
    private PositionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PositionValidator();
    }

    public function testRookCheck(): void
    {
        $p = new Position();
        // Place white rook on e1 and black king on e8, white to move.
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_ROOK);
        $p->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testRookBlocked(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_ROOK);
        $p->setPieceAt(CoordinatesEnum::E2, PieceEnum::WHITE_PAWN); // Block the rook
        $p->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertFalse($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testKnightCheck(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::F6, PieceEnum::WHITE_KNIGHT);
        $p->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testPawnCheck(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::D4, PieceEnum::WHITE_PAWN);
        $p->setPieceAt(CoordinatesEnum::D1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E5, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testKingCheck(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E2, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E3, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testCheckAgainstWhiteKingWhenBlackToMove(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_QUEEN);
        $p->setPieceAt(CoordinatesEnum::D8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::BLACK);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::KING_IN_CHECK));
    }

    public function testNoWhiteKing(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::NO_WHITE_KING));
    }

    public function testNoBlackKing(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::NO_BLACK_KING));
    }

    public function testMultipleWhiteKings(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E2, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::MULTIPLE_WHITE_KINGS));
    }

    public function testMultipleBlackKings(): void
    {
        $p = new Position();
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        $p->setPieceAt(CoordinatesEnum::E7, PieceEnum::BLACK_KING);
        $p->setSideToMove(ColorEnum::WHITE);

        self::assertTrue($this->positionHasViolation($p, PositionViolationEnum::MULTIPLE_BLACK_KINGS));
    }

    public function testValidPositions(): void
    {
        $p = Position::fromFEN(PGNParser::INITIAL_FEN);
        self::assertEmpty($this->validator->validate($p));

        $p = Position::fromFEN('8/8/8/8/8/8/8/8 w - - 0 1');
        $p->setPieceAt(CoordinatesEnum::E1, PieceEnum::WHITE_KING);
        $p->setPieceAt(CoordinatesEnum::E8, PieceEnum::BLACK_KING);
        self::assertEmpty($this->validator->validate($p));
    }

    private function positionHasViolation(Position $position, PositionViolationEnum ...$violation): bool
    {
        $violations = $this->validator->validate($position);
        foreach ($violation as $v) {
            if (!in_array($v, $violations, true)) {
                return false;
            }
        }

        return true;
    }
}
