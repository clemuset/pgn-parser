<?php

namespace Cmuset\PgnParser\Tests\Exporter;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exporter\MoveExporter;
use Cmuset\PgnParser\Model\Move;
use PHPUnit\Framework\TestCase;

class MoveExporterTest extends TestCase
{
    private MoveExporter $exporter;

    protected function setUp(): void
    {
        $this->exporter = new MoveExporter();
    }

    public function testExportSimplePawnMove(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $move->setTo(SquareEnum::E4);
        self::assertSame('e4', $this->exporter->export($move));
    }

    public function testExportCaptureWithCheck(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_BISHOP);
        $move->setFileFrom('c');
        $move->setRowFrom(4);
        $move->setIsCapture(true);
        $move->setTo(SquareEnum::F7);
        $move->setIsCheck(true);
        self::assertSame('Bc4xf7+', $this->exporter->export($move));
    }

    public function testExportPawnCaptureWithCheckmatePromotion(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $move->setFileFrom('e');
        $move->setIsCapture(true);
        $move->setTo(SquareEnum::D8);
        $move->setPromotion(PieceEnum::WHITE_QUEEN);
        $move->setIsCheckmate(true);
        self::assertSame('exd8=Q#', $this->exporter->export($move));
    }

    public function testExportPromotionMateWithoutFileFrom(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_PAWN);
        $move->setIsCapture(true);
        $move->setTo(SquareEnum::H8);
        $move->setPromotion(PieceEnum::WHITE_QUEEN);
        $move->setIsCheckmate(true);
        self::assertSame('xh8=Q#', $this->exporter->export($move));
    }

    public function testExportKingsideCastling(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::WHITE_KING);
        $move->setCastling(CastlingEnum::WHITE_KINGSIDE);
        self::assertSame('O-O', $this->exporter->export($move));
    }

    public function testExportQueensideCastling(): void
    {
        $move = new Move();
        $move->setPiece(PieceEnum::BLACK_KING);
        $move->setCastling(CastlingEnum::BLACK_QUEENSIDE);
        self::assertSame('O-O-O', $this->exporter->export($move));
    }
}
