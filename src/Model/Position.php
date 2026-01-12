<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Exporter\PositionExporter;
use Cmuset\PgnParser\MoveApplier\MoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\BishopMoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\KingMoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\KnightMoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\PawnMoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\QueenMoveApplier;
use Cmuset\PgnParser\MoveApplier\PieceMoveApplier\RookMoveApplier;
use Cmuset\PgnParser\Parser\FENParser;

class Position
{
    /** @var array<string, Square> */
    private array $squares = [];
    private ColorEnum $sideToMove = ColorEnum::WHITE;

    /** @var CastlingEnum[] */
    private array $castlingRights = [
        CastlingEnum::WHITE_KINGSIDE,
        CastlingEnum::WHITE_QUEENSIDE,
        CastlingEnum::BLACK_KINGSIDE,
        CastlingEnum::BLACK_QUEENSIDE,
    ];
    private ?CoordinatesEnum $enPassantTarget = null;
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
        foreach (CoordinatesEnum::cases() as $coordinates) {
            $this->squares[$coordinates->value] = new Square($coordinates);
        }
    }

    public function getSquares(): array
    {
        return $this->squares;
    }

    public function getSquare(CoordinatesEnum $coordinates): Square
    {
        return $this->squares[$coordinates->value];
    }

    public function getSideToMove(): ColorEnum
    {
        return $this->sideToMove;
    }

    public function setSideToMove(ColorEnum $sideToMove): void
    {
        $this->sideToMove = $sideToMove;
    }

    public function toggleSideToMove(): void
    {
        $this->sideToMove = $this->sideToMove->opposite();
    }

    public function getCastlingRights(): array
    {
        return $this->castlingRights;
    }

    public function setCastlingRights(array $castlingRights): void
    {
        $this->castlingRights = $castlingRights;
    }

    public function removeCastlingRight(CastlingEnum $castling): void
    {
        $this->castlingRights = array_filter(
            $this->castlingRights,
            fn (CastlingEnum $right) => $right !== $castling
        );
    }

    public function hasCastlingRight(CastlingEnum $castling): bool
    {
        return in_array($castling, $this->castlingRights);
    }

    public function getEnPassantTarget(): ?CoordinatesEnum
    {
        return $this->enPassantTarget;
    }

    public function setEnPassantTarget(?CoordinatesEnum $enPassantTarget): void
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

    public function setPieceAt(CoordinatesEnum $square, ?PieceEnum $piece): void
    {
        if (!isset($this->squares[$square->value])) {
            throw new \LogicException('Square not initialized: ' . $square->value);
        }

        $this->squares[$square->value]->setPiece($piece);
    }

    public function getPieceAt(CoordinatesEnum $square): ?PieceEnum
    {
        if (!isset($this->squares[$square->value])) {
            throw new \LogicException('Square not initialized: ' . $square->value);
        }

        return $this->squares[$square->value]->getPiece();
    }

    public function applyMove(string|Move $move): void
    {
        $move = is_string($move) ? Move::fromSAN($move, $this->sideToMove) : $move;

        MoveApplier::create()->apply($this, $move);
    }

    /**
     * @return Square[]
     */
    public function find(PieceEnum ...$piece): array
    {
        return array_filter($this->squares, fn (Square $square) => in_array($square->getPiece(), $piece));
    }

    /**
     * @return Square[]
     */
    public function findByFile(PieceEnum $piece, string $file): array
    {
        return array_filter(
            $this->find($piece),
            fn (Square $square) => $square->getCoordinates()->file() === $file,
        );
    }

    public function findByRank(PieceEnum $piece, int $rank): array
    {
        return array_filter(
            $this->find($piece),
            fn (Square $square) => $square->getCoordinates()->rank() === $rank,
        );
    }

    public function findOne(PieceEnum $piece): ?Square
    {
        return array_values($this->find($piece))[0] ?? null;
    }

    /**
     * @return Square[]
     */
    public function findAttackers(Square|CoordinatesEnum $square, ColorEnum $attackerColor): array
    {
        $square = $square instanceof Square ? $square : $this->getSquare($square);

        $attackers = [];
        foreach ($this->squares as $from) {
            $piece = $from->getPiece();

            if (null === $piece || $piece->color() === $square->getPiece()?->color() || $piece->color() !== $attackerColor) {
                continue;
            }

            if ($this->isAttacking($piece, $from->getCoordinates(), $square->getCoordinates())) {
                $attackers[] = $from;
            }
        }

        return $attackers;
    }

    public function hasAttacker(Square|CoordinatesEnum $square, ColorEnum $attackerColor): bool
    {
        return count($this->findAttackers($square, $attackerColor)) > 0;
    }

    public function castlingIsAllowed(CastlingEnum $castling): bool
    {
        $isGoodColor = match ($castling) {
            CastlingEnum::WHITE_KINGSIDE, CastlingEnum::WHITE_QUEENSIDE => ColorEnum::WHITE === $this->sideToMove,
            CastlingEnum::BLACK_KINGSIDE, CastlingEnum::BLACK_QUEENSIDE => ColorEnum::BLACK === $this->sideToMove,
        };

        return $isGoodColor && $this->hasCastlingRight($castling);
    }

    public function getLegalMoves(): array
    {
        $legalMoves = [];
        foreach ($this->iterateSquaresWithPiece($this->getSideToMove()) as $fromSquare) {
            $piece = $fromSquare->getPiece();
            $move = new Move();
            $move->setPiece($piece);
            $move->setSquareFrom($fromSquare->getCoordinates());

            foreach (CoordinatesEnum::cases() as $coordinates) {
                $cloneMove = clone $move;
                $cloneMove->setTo($coordinates);

                try {
                    (clone $this)->applyMove($cloneMove);
                    $legalMoves[] = $cloneMove;
                } catch (\Exception) {
                }
            }
        }

        foreach ($this->castlingRights as $castlingRight) {
            $move = new Move();
            $move->setPiece(
                match ($castlingRight) {
                    CastlingEnum::WHITE_KINGSIDE, CastlingEnum::WHITE_QUEENSIDE => PieceEnum::WHITE_KING,
                    CastlingEnum::BLACK_KINGSIDE, CastlingEnum::BLACK_QUEENSIDE => PieceEnum::BLACK_KING,
                }
            );
            $move->setCastling($castlingRight);

            try {
                (clone $this)->applyMove($move);
                $legalMoves[] = $move;
            } catch (\Exception) {
            }
        }

        return $legalMoves;
    }

    private function isAttacking(PieceEnum $piece, CoordinatesEnum $from, CoordinatesEnum $to): bool
    {
        if ($from === $to) {
            return false;
        }

        $pieceMoveApplier = match ($piece) {
            PieceEnum::WHITE_PAWN, PieceEnum::BLACK_PAWN => new PawnMoveApplier(),
            PieceEnum::WHITE_KNIGHT, PieceEnum::BLACK_KNIGHT => new KnightMoveApplier(),
            PieceEnum::WHITE_BISHOP, PieceEnum::BLACK_BISHOP => new BishopMoveApplier(),
            PieceEnum::WHITE_ROOK, PieceEnum::BLACK_ROOK => new RookMoveApplier(),
            PieceEnum::WHITE_QUEEN, PieceEnum::BLACK_QUEEN => new QueenMoveApplier(),
            PieceEnum::WHITE_KING, PieceEnum::BLACK_KING => new KingMoveApplier(),
        };

        return $pieceMoveApplier->isAttacking($from, $to, $this);
    }

    /**
     * @return iterable<Square>
     */
    public function iterateSquaresWithPiece(?ColorEnum $color = null): iterable
    {
        foreach ($this->squares as $square) {
            $piece = $square->getPiece();

            if (null !== $piece && (null === $color || $piece->color() === $color)) {
                yield $square;
            }
        }
    }

    public function isCheckmate(): bool
    {
        $kingPiece = PieceEnum::king($this->sideToMove);
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare || !$this->hasAttacker($kingSquare, $this->sideToMove->opposite())) {
            return false;
        }

        $legalMoves = $this->getLegalMoves();

        return 0 === count($legalMoves);
    }

    public function isCheck(): bool
    {
        $kingPiece = PieceEnum::king($this->sideToMove);
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare) {
            return false;
        }

        return $this->hasAttacker($kingSquare, $this->sideToMove->opposite());
    }

    public function isStaleMate(): bool
    {
        $kingPiece = PieceEnum::king($this->sideToMove);
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare || $this->hasAttacker($kingSquare, $this->sideToMove->opposite())) {
            return false;
        }

        $legalMoves = $this->getLegalMoves();

        return 0 === count($legalMoves);
    }

    public function dump(): string
    {
        $output = '';
        for ($rank = 8; $rank >= 1; --$rank) {
            for ($file = 'a'; $file <= 'h'; ++$file) {
                $coordinates = CoordinatesEnum::from($file . $rank);
                $piece = $this->getPieceAt($coordinates);
                $output .= $piece ? $piece->value : '.';
                $output .= ' ';
            }
            $output .= "\n";
        }

        return $output;
    }

    public function __clone(): void
    {
        $this->squares = array_map(fn (Square $square) => clone $square, $this->squares);
    }
}
