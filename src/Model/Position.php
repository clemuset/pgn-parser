<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Enum\SquareEnum;
use Cmuset\PgnParser\Exporter\PositionExporter;
use Cmuset\PgnParser\MoveApplier\MoveApplier;
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

    public function getSquare(SquareEnum $squareEnum): Square
    {
        return $this->squares[$squareEnum->value];
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
        $this->sideToMove = ColorEnum::WHITE === $this->sideToMove ? ColorEnum::BLACK : ColorEnum::WHITE;
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

    public function applyMove(Move $move): Position
    {
        return new MoveApplier()->apply($this, $move);
    }

    /**
     * @return Square[]
     */
    public function find(PieceEnum $piece): array
    {
        return array_filter($this->squares, fn (Square $square) => $square->getPiece() === $piece);
    }

    public function findOne(PieceEnum $piece): ?Square
    {
        return array_values($this->find($piece))[0] ?? null;
    }

    public function findPawnMovableAt(SquareEnum $square): ?Square
    {
        // Forward-only pawn moves to an empty target (one or two steps from the starting rank)
        if (null !== $this->getPieceAt($square)) {
            return null;
        }

        $file = $square->file();
        $rank = $square->rank();

        if (ColorEnum::WHITE === $this->sideToMove) {
            // One-step white
            $oneStepWhite = SquareEnum::tryFrom($file . ($rank - 1));

            if ($oneStepWhite && PieceEnum::WHITE_PAWN === $this->getPieceAt($oneStepWhite)) {
                return $this->getSquare($oneStepWhite);
            }

            // Two-step white (from rank 2 to rank 4, path must be clear)
            if (4 === $rank) {
                $from = SquareEnum::tryFrom($file . '2');

                if ($from && $this->isPathClear($from, $square) && PieceEnum::WHITE_PAWN === $this->getPieceAt($from)) {
                    return $this->getSquare($from);
                }
            }
        }

        if (ColorEnum::BLACK === $this->sideToMove) {
            // One-step black
            $oneStepBlack = SquareEnum::tryFrom($file . ($rank + 1));

            if ($oneStepBlack && PieceEnum::BLACK_PAWN === $this->getPieceAt($oneStepBlack)) {
                return $this->getSquare($oneStepBlack);
            }

            // Two-step black (from rank 7 to rank 5, path must be clear)
            if (5 === $rank) {
                $from = SquareEnum::tryFrom($file . '7');

                if ($from && $this->isPathClear($from, $square) && PieceEnum::BLACK_PAWN === $this->getPieceAt($from)) {
                    return $this->getSquare($from);
                }
            }
        }

        return null;
    }

    /**
     * @return Square[]
     */
    public function findAttackers(Square|SquareEnum $square): array
    {
        $square = $square instanceof Square ? $square : $this->getSquare($square);

        $attackers = [];
        foreach ($this->squares as $from) {
            $piece = $from->getPiece();

            if (null === $piece) {
                continue;
            }

            if ($piece->color() === $square->getPiece()?->color()) {
                continue; // same color
            }

            if ($this->isAttacking($piece, $from->getSquare(), $square->getSquare())) {
                $attackers[] = $from;
            }
        }

        return $attackers;
    }

    public function hasAttacker(Square|SquareEnum $square): bool
    {
        return count($this->findAttackers($square)) > 0;
    }

    public function castlingWayIsAttacking(CastlingEnum $castlingEnum): bool
    {
        switch ($castlingEnum) {
            case CastlingEnum::WHITE_KINGSIDE:
                return $this->hasAttacker(SquareEnum::E1)
                    || $this->hasAttacker(SquareEnum::F1)
                    || $this->hasAttacker(SquareEnum::G1);
            case CastlingEnum::WHITE_QUEENSIDE:
                return $this->hasAttacker(SquareEnum::E1)
                    || $this->hasAttacker(SquareEnum::D1)
                    || $this->hasAttacker(SquareEnum::C1);
            case CastlingEnum::BLACK_KINGSIDE:
                return $this->hasAttacker(SquareEnum::E8)
                    || $this->hasAttacker(SquareEnum::F8)
                    || $this->hasAttacker(SquareEnum::G8);
            case CastlingEnum::BLACK_QUEENSIDE:
                return $this->hasAttacker(SquareEnum::E8)
                    || $this->hasAttacker(SquareEnum::D8)
                    || $this->hasAttacker(SquareEnum::C8);
            default:
                throw new \RuntimeException('Invalid castling enum');
        }
    }

    public function castlingWayIsBlocked(CastlingEnum $castling): bool
    {
        switch ($castling) {
            case CastlingEnum::WHITE_KINGSIDE:
                return null !== $this->getPieceAt(SquareEnum::F1)
                    || null !== $this->getPieceAt(SquareEnum::G1);
            case CastlingEnum::WHITE_QUEENSIDE:
                return null !== $this->getPieceAt(SquareEnum::D1)
                    || null !== $this->getPieceAt(SquareEnum::C1)
                    || null !== $this->getPieceAt(SquareEnum::B1);
            case CastlingEnum::BLACK_KINGSIDE:
                return null !== $this->getPieceAt(SquareEnum::F8)
                    || null !== $this->getPieceAt(SquareEnum::G8);
            case CastlingEnum::BLACK_QUEENSIDE:
                return null !== $this->getPieceAt(SquareEnum::D8)
                    || null !== $this->getPieceAt(SquareEnum::C8)
                    || null !== $this->getPieceAt(SquareEnum::B8);
            default:
                throw new \RuntimeException('Invalid castling enum');
        }
    }

    public function castlingIsAllowed(CastlingEnum $castling): bool
    {
        $isGoodColor = match ($castling) {
            CastlingEnum::WHITE_KINGSIDE, CastlingEnum::WHITE_QUEENSIDE => ColorEnum::WHITE === $this->sideToMove,
            CastlingEnum::BLACK_KINGSIDE, CastlingEnum::BLACK_QUEENSIDE => ColorEnum::BLACK === $this->sideToMove,
        };

        return $isGoodColor
            && $this->hasCastlingRight($castling)
            && !$this->castlingWayIsBlocked($castling)
            && !$this->castlingWayIsAttacking($castling);
    }

    public function getLegalMoves(): array
    {
        $applier = new MoveApplier(verifyCheckmate: false);

        $legalMoves = [];
        foreach ($this->iterateSquaresWithPiece($this->getSideToMove()) as $fromSquare) {
            $piece = $fromSquare->getPiece();
            $move = new Move();
            $move->setPiece($piece);
            $move->setSquareFrom($fromSquare->getSquare());

            foreach (SquareEnum::cases() as $toSquareEnum) {
                $cloneMove = clone $move;
                $cloneMove->setTo($toSquareEnum);

                try {
                    $applier->apply($this, $cloneMove);
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
                $applier->apply($this, $move);
                $legalMoves[] = $move;
            } catch (\Exception) {
            }
        }

        return $legalMoves;
    }

    private function isAttacking(PieceEnum $piece, SquareEnum $from, SquareEnum $to): bool
    {
        if ($from === $to) {
            return false;
        }

        $diffFile = ord($to->file()) - ord($from->file());
        $diffRank = $to->rank() - $from->rank();

        switch ($piece) {
            case PieceEnum::WHITE_PAWN:
                return 1 === $diffRank && 1 === abs($diffFile) && (null !== $this->getPieceAt($to) || $to === $this->enPassantTarget);
            case PieceEnum::BLACK_PAWN:
                return -1 === $diffRank && 1 === abs($diffFile) && (null !== $this->getPieceAt($to) || $to === $this->enPassantTarget);
            case PieceEnum::WHITE_KNIGHT:
            case PieceEnum::BLACK_KNIGHT:
                return in_array([$diffFile, $diffRank], [[1, 2], [2, 1], [-1, 2], [-2, 1], [1, -2], [2, -1], [-1, -2], [-2, -1]], true);
            case PieceEnum::WHITE_BISHOP:
            case PieceEnum::BLACK_BISHOP:
                if (abs($diffFile) === abs($diffRank)) {
                    return $this->isPathClear($from, $to);
                }

                return false;
            case PieceEnum::WHITE_ROOK:
            case PieceEnum::BLACK_ROOK:
                if (0 === $diffFile || 0 === $diffRank) {
                    return $this->isPathClear($from, $to);
                }

                return false;
            case PieceEnum::WHITE_QUEEN:
            case PieceEnum::BLACK_QUEEN:
                if (0 === $diffFile || 0 === $diffRank || abs($diffFile) === abs($diffRank)) {
                    return $this->isPathClear($from, $to);
                }

                return false;
            case PieceEnum::WHITE_KING:
            case PieceEnum::BLACK_KING:
                return 1 === max(abs($diffFile), abs($diffRank));
            default:
                return false;
        }
    }

    public function isPathClear(SquareEnum $from, SquareEnum $to): bool
    {
        $fileStep = $this->sign(ord($to->file()) - ord($from->file()));
        $rankStep = $this->sign($to->rank() - $from->rank());

        $currentFileOrd = ord($from->file()) + $fileStep;
        $currentRank = $from->rank() + $rankStep;

        while ($currentFileOrd !== ord($to->file()) || $currentRank !== $to->rank()) {
            $squareName = chr($currentFileOrd) . $currentRank;
            $squareEnum = SquareEnum::tryFrom($squareName);

            if (!$squareEnum) {
                return false; // out of board
            }

            if (null !== $this->getPieceAt($squareEnum)) {
                return false; // piece blocking the path
            }

            $currentFileOrd += $fileStep;
            $currentRank += $rankStep;
        }

        return true;
    }

    private function sign(int $value): int
    {
        if (0 === $value) {
            return 0;
        }

        return $value > 0 ? 1 : -1;
    }

    public function dump(): string
    {
        $output = '';
        for ($rank = 8; $rank >= 1; --$rank) {
            for ($file = 'a'; $file <= 'h'; ++$file) {
                $squareEnum = SquareEnum::from($file . $rank);
                $piece = $this->getPieceAt($squareEnum);
                $output .= $piece ? $piece->value : '.';
                $output .= ' ';
            }
            $output .= "\n";
        }

        return $output;
    }

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
        $kingPiece = ColorEnum::WHITE === $this->sideToMove ? PieceEnum::WHITE_KING : PieceEnum::BLACK_KING;
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare || !$this->hasAttacker($kingSquare)) {
            return false;
        }

        $legalMoves = $this->getLegalMoves();

        return 0 === count($legalMoves);
    }

    public function isCheck(): bool
    {
        $kingPiece = ColorEnum::WHITE === $this->sideToMove ? PieceEnum::WHITE_KING : PieceEnum::BLACK_KING;
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare) {
            return false;
        }

        return $this->hasAttacker($kingSquare);
    }

    public function isStaleMate(): bool
    {
        $kingPiece = ColorEnum::WHITE === $this->sideToMove ? PieceEnum::WHITE_KING : PieceEnum::BLACK_KING;
        $kingSquare = $this->findOne($kingPiece);

        if (null === $kingSquare || $this->hasAttacker($kingSquare)) {
            return false;
        }

        $legalMoves = $this->getLegalMoves();

        return 0 === count($legalMoves);
    }

    public function __clone(): void
    {
        $this->squares = array_map(fn (Square $square) => clone $square, $this->squares);
    }
}
