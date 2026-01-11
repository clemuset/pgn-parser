<?php

namespace Cmuset\PgnParser\Parser;

use Cmuset\PgnParser\Enum\CastlingEnum;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;
use Cmuset\PgnParser\Exception\SANParsingException;
use Cmuset\PgnParser\Model\Move;

class SANParser implements SANParserInterface
{
    public const string SAN_PATTERN = '/^(O-O(-O)?|0-0(-0)?|[KQRBN]?[a-h]?[1-8]?x?[a-h]?[1-8](=[QRBN])?[+#]?)(\s*(!!|!\?|\?!|\?\?|!|\?))?$/';
    private const array ANNOTATIONS = ['!!', '!?', '?!', '??', '!', '?'];

    public function parse(string $san, ColorEnum $color): Move
    {
        $san = $this->removeEnPassantIndicator($san);

        if (1 !== preg_match(self::SAN_PATTERN, $san)) {
            throw new SANParsingException('Invalid SAN string: "' . $san . '"');
        }

        $move = new Move();
        $move->setPiece($this->mapPieceLetter($this->extractPieceMoved($san), $color));

        if ($to = $this->extractDestinationSquare($san)) {
            $move->setTo(CoordinatesEnum::from($to));
        }

        if ($disamb = $this->extractDisambiguationPart($san)) {
            $move->setSquareFrom(CoordinatesEnum::tryFrom($disamb));
        }

        $move->setFileFrom($this->extractFileFrom($san));
        $move->setRowFrom($this->extractRowFrom($san));
        $move->setIsCapture($this->isCapture($san));
        $move->setIsCheck($this->isCheck($san));
        $move->setIsCheckmate($this->isCheckmate($san));
        $move->setCastling(match (true) {
            $this->isKingsideCastling($san) => CastlingEnum::kingside($color),
            $this->isQueensideCastling($san) => CastlingEnum::queenside($color),
            default => null,
        });

        if ($this->isPromotion($san)) {
            $move->setPromotion($this->mapPieceLetter($this->extractPromotionPiece($san), $color));
        }

        $move->setAnnotation($this->extractAnnotation($san));

        return $move;
    }

    private function mapPieceLetter(string $letter, ColorEnum $color): PieceEnum
    {
        return match (strtoupper($letter)) {
            'K' => PieceEnum::king($color),
            'Q' => PieceEnum::queen($color),
            'R' => PieceEnum::rook($color),
            'B' => PieceEnum::bishop($color),
            'N' => PieceEnum::knight($color),
            'P' => PieceEnum::pawn($color),
            default => throw new SANParsingException('Unknown piece letter: ' . $letter),
        };
    }

    private function removeEnPassantIndicator(string $san): string
    {
        return trim(str_replace('e.p.', '', $san));
    }

    private function isKingsideCastling(string $san): bool
    {
        return in_array($this->cleanSan($san), ['O-O', '0-0'], true);
    }

    private function isQueensideCastling(string $san): bool
    {
        return in_array($this->cleanSan($san), ['O-O-O', '0-0-0'], true);
    }

    private function isCastling(string $san): bool
    {
        return $this->isKingsideCastling($san) || $this->isQueensideCastling($san);
    }

    private function isCapture(string $san): bool
    {
        return str_contains(strtolower($san), 'x');
    }

    private function isPromotion(string $san): bool
    {
        return str_contains($san, '=');
    }

    private function isCheck(string $san): bool
    {
        return str_ends_with($this->removeEnPassantIndicator($san), '+');
    }

    private function isCheckmate(string $san): bool
    {
        return str_ends_with($this->removeEnPassantIndicator($san), '#');
    }

    private function extractPieceMoved(string $san): string
    {
        $san = $this->cleanSan($san, true);

        // Handle castling
        if ($this->isCastling($san)) {
            return 'K'; // Castling always involves the king
        }

        // The piece moved is typically the first character if it's a piece identifier
        $pieceIdentifiers = ['K', 'Q', 'R', 'B', 'N'];

        if (in_array($san[0], $pieceIdentifiers, true)) {
            return $san[0];
        }

        // If no piece identifier, it's a pawn move
        return 'P';
    }

    private function extractPromotionPiece(string $san): ?string
    {
        $san = $this->cleanSan($san);

        if ($this->isPromotion($san)) {
            return explode('=', $san)[1];
        }

        return null;
    }

    private function extractDestinationSquare(string $san): ?string
    {
        $san = $this->cleanSan($san, true);

        // Handle castling
        if ($this->isCastling($san)) {
            return null; // Castling does not have a destination square in SAN
        }

        // The destination square is typically the last two characters
        $length = strlen($san);

        if ($length >= 2) {
            return substr($san, -2);
        }

        return null;
    }

    private function extractDisambiguationPart(string $san): ?string
    {
        $san = $this->cleanSan($san, true);

        // Handle castling
        if ($this->isCastling($san)) {
            return null; // Castling does not have disambiguation in SAN
        }

        // The destination square is typically the last two characters
        $length = strlen($san);

        if ($length <= 2) {
            return null; // No disambiguation part
        }

        // Extract the part before the destination square
        $disambiguationPart = substr($san, 0, $length - 2);

        // Remove piece identifier if present
        $pieceIdentifiers = ['K', 'Q', 'R', 'B', 'N'];

        if (in_array($disambiguationPart[0], $pieceIdentifiers, true)) {
            $disambiguationPart = substr($disambiguationPart, 1);
        }

        return '' !== $disambiguationPart ? $disambiguationPart : null;
    }

    private function extractFileFrom(string $san): ?string
    {
        $disambiguation = $this->extractDisambiguationPart($san);

        if (null === $disambiguation) {
            return null;
        }

        // Check if disambiguation contains a file (a-h)
        if (preg_match('/[a-h]/', $disambiguation, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function extractRowFrom(string $san): ?int
    {
        $disambiguation = $this->extractDisambiguationPart($san);

        if (null === $disambiguation) {
            return null;
        }

        // Check if disambiguation contains a rank (1-8)
        if (preg_match('/[1-8]/', $disambiguation, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function extractAnnotation(string $san): ?string
    {
        return array_find(self::ANNOTATIONS, fn (string $annotation) => str_ends_with($san, $annotation));
    }

    private function cleanSan(string $san, bool $removePromotionPart = false): string
    {
        // Remove en passant indicator (if any)
        $san = $this->removeEnPassantIndicator($san);

        // Remove any check or checkmate indicators
        $san = rtrim(trim($san), '+#');

        // Remove capture indicator
        $san = str_replace('x', '', $san);

        // Remove annotations
        foreach (self::ANNOTATIONS as $annotation) {
            if (str_ends_with($san, $annotation)) {
                $san = substr($san, 0, -strlen($annotation));
                break;
            }
        }

        // Optionally remove promotion part
        if ($removePromotionPart && $this->isPromotion($san)) {
            $parts = explode('=', $san);
            $san = $parts[0];
        }

        return trim($san);
    }
}
