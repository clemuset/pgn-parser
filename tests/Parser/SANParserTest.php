<?php

namespace Cmuset\PgnParser\Tests\Parser;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Exception\SANParsingException;
use Cmuset\PgnParser\Parser\SANParser;
use PHPUnit\Framework\TestCase;

class SANParserTest extends TestCase
{
    private SANParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SANParser();
    }

    public function testParseSimplePawnMove(): void
    {
        $move = $this->parser->parse('e4', ColorEnum::WHITE);
        self::assertSame(PieceEnum::WHITE_PAWN, $move->getPiece());
        self::assertSame('e4', $move->getTo()?->value);
        self::assertFalse($move->isCapture());
        self::assertFalse($move->isCheck());
        self::assertFalse($move->isCheckmate());
        self::assertNull($move->getAnnotation());
    }

    public function testParsePieceMoveWithDisambiguationFile(): void
    {
        $move = $this->parser->parse('Nbd7', ColorEnum::BLACK);
        self::assertSame(PieceEnum::BLACK_KNIGHT, $move->getPiece());
        self::assertSame('d7', $move->getTo()?->value);
        self::assertSame('b', $move->getFileFrom());
        self::assertNull($move->getRowFrom());
    }

    public function testParsePieceMoveWithDisambiguationRank(): void
    {
        $move = $this->parser->parse('N1d7', ColorEnum::WHITE);
        self::assertSame(PieceEnum::WHITE_KNIGHT, $move->getPiece());
        self::assertSame(1, $move->getRowFrom());
    }

    public function testParseCaptureWithCheck(): void
    {
        $move = $this->parser->parse('Qxe5+', ColorEnum::WHITE);
        self::assertTrue($move->isCapture());
        self::assertTrue($move->isCheck());
        self::assertFalse($move->isCheckmate());
        self::assertSame('e5', $move->getTo()?->value);
        self::assertSame(PieceEnum::WHITE_QUEEN, $move->getPiece());
    }

    public function testParsePromotionWithMate(): void
    {
        $move = $this->parser->parse('exd8=Q#', ColorEnum::WHITE);
        self::assertTrue($move->isCapture());
        self::assertTrue($move->isCheckmate());
        self::assertSame(PieceEnum::WHITE_QUEEN, $move->getPromotion());
    }

    public function testParseKingsideCastling(): void
    {
        $move = $this->parser->parse('O-O', ColorEnum::WHITE);
        self::assertTrue($move->isCastling());
        self::assertSame(CastlingEnum::WHITE_KINGSIDE, $move->getCastling());
    }

    public function testParseQueensideCastlingZeroNotation(): void
    {
        $move = $this->parser->parse('0-0-0', ColorEnum::BLACK);
        self::assertTrue($move->isCastling());
        self::assertSame(CastlingEnum::BLACK_QUEENSIDE, $move->getCastling());
    }

    public function testAnnotationExtraction(): void
    {
        $move = $this->parser->parse('e4!', ColorEnum::WHITE);
        self::assertSame('!', $move->getAnnotation());
    }

    public function testEnPassantIndicatorRemoval(): void
    {
        $move = $this->parser->parse('exd6 e.p.', ColorEnum::WHITE);
        self::assertTrue($move->isCapture());
        self::assertSame('d6', $move->getTo()?->value);
        self::assertNull($move->getAnnotation());
    }

    public function testInvalidSanThrows(): void
    {
        $this->expectException(SANParsingException::class);
        $this->parser->parse('Invalid$', ColorEnum::WHITE);
    }
}
