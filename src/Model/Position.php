<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exporter\PositionExporter;
use Cmuset\PgnParser\Parser\FENParser;

class Position
{
    /** @var array<string, Square> */
    private array $squares = [];
    private ColorEnum $sideToMove = ColorEnum::WHITE;
    private array $castlingRights = [
        CastlingEnum::WHITE_KINGSIDE,
        CastlingEnum::WHITE_QUEENSIDE,
        CastlingEnum::BLACK_KINGSIDE,
        CastlingEnum::BLACK_QUEENSIDE,
    ];
    private ?SquareEnum $enPassantTarget = null;
    private int $halfmoveClock = 0;
    private int $fullmoveNumber = 1;

    public function __construct()
    {
        $this->initSquares();
    }

    public static function fromFEN(string $fen): Position
    {
        return new FENParser()->parse($fen);
    }

    public function getFEN(): string
    {
        return new PositionExporter()->export($this);
    }

    private function initSquares(): void
    {
        foreach (SquareEnum::cases() as $squareEnum) {
            $this->squares[$squareEnum->value] = new Square($squareEnum);
        }
    }

    public function getSquares(): array
    {
        return $this->squares;
    }

    public function getSideToMove(): ColorEnum
    {
        return $this->sideToMove;
    }

    public function setSideToMove(ColorEnum $sideToMove): void
    {
        $this->sideToMove = $sideToMove;
    }

    public function getCastlingRights(): array
    {
        return $this->castlingRights;
    }

    public function setCastlingRights(array $castlingRights): void
    {
        $this->castlingRights = $castlingRights;
    }

    public function getEnPassantTarget(): ?SquareEnum
    {
        return $this->enPassantTarget;
    }

    public function setEnPassantTarget(?SquareEnum $enPassantTarget): void
    {
        $this->enPassantTarget = $enPassantTarget;
    }

    public function getHalfmoveClock(): int
    {
        return $this->halfmoveClock;
    }

    public function setHalfmoveClock(int $halfmoveClock): void
    {
        $this->halfmoveClock = $halfmoveClock;
    }

    public function getFullmoveNumber(): int
    {
        return $this->fullmoveNumber;
    }

    public function setFullmoveNumber(int $fullmoveNumber): void
    {
        $this->fullmoveNumber = $fullmoveNumber;
    }

    public function setPieceAt(SquareEnum $square, ?PieceEnum $piece): void
    {
        if (!isset($this->squares[$square->value])) {
            throw new \LogicException('Square not initialized: ' . $square->value);
        }

        $this->squares[$square->value]->setPiece($piece);
    }

    public function getPieceAt(SquareEnum $square): ?PieceEnum
    {
        if (!isset($this->squares[$square->value])) {
            throw new \LogicException('Square not initialized: ' . $square->value);
        }

        return $this->squares[$square->value]->getPiece();
    }
}
