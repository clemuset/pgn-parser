<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CommentAnchorEnum;

class MoveNode
{
    private ?Move $move;
    private ?int $moveNumber;
    private ?Move $resolvedMove = null;
    private ?string $afterMoveComment = null;
    private ?string $beforeMoveComment = null;

    /** @var int[] */
    private array $nags = [];

    /** @var array<Variation> */
    private array $variations = [];

    public function __construct(string|Move|null $move = null, ?int $moveNumber = null)
    {
        $this->move = is_string($move) ? Move::fromSAN($move) : $move;
        $this->moveNumber = $moveNumber;
    }

    public function getMove(): ?Move
    {
        return $this->move;
    }

    public function setMove(?Move $move): void
    {
        $this->move = $move;
    }

    public function getMoveNumber(): ?int
    {
        return $this->moveNumber;
    }

    public function setMoveNumber(?int $moveNumber): void
    {
        $this->moveNumber = $moveNumber;
    }

    public function getResolvedMove(): ?Move
    {
        return $this->resolvedMove;
    }

    public function setResolvedMove(?Move $move): void
    {
        $this->resolvedMove = $move;
    }

    public function getColor(): ?ColorEnum
    {
        return $this->move?->getPiece()?->color();
    }

    public function getBeforeMoveComment(): ?string
    {
        return $this->beforeMoveComment;
    }

    public function setBeforeMoveComment(?string $comment): void
    {
        $this->beforeMoveComment = $comment;
    }

    public function getAfterMoveComment(): ?string
    {
        return $this->afterMoveComment;
    }

    public function setAfterMoveComment(?string $comment): void
    {
        $this->afterMoveComment = $comment;
    }

    public function getComment(CommentAnchorEnum $anchor = CommentAnchorEnum::POST): ?string
    {
        return CommentAnchorEnum::PRE === $anchor ? $this->getBeforeMoveComment() : $this->getAfterMoveComment();
    }

    public function setComment(?string $comment, CommentAnchorEnum $anchor = CommentAnchorEnum::POST): void
    {
        if (CommentAnchorEnum::PRE === $anchor) {
            $this->setBeforeMoveComment($comment);
        } else {
            $this->setAfterMoveComment($comment);
        }
    }

    public function getNags(): array
    {
        return $this->nags;
    }

    public function setNags(array $nags): void
    {
        $this->nags = array_map(fn ($nag) => (int) $nag, $nags);
    }

    public function addNag(int $nag): void
    {
        if (!in_array($nag, $this->nags, true)) {
            $this->nags[] = $nag;
        }
    }

    /** @return array<Variation> */
    public function getVariations(): array
    {
        return $this->variations;
    }

    public function addVariation(Variation $variationLine): void
    {
        $this->variations[] = $variationLine;
    }

    public function getKey(): string
    {
        $moveNumber = $this->getMoveNumber();
        $colorSuffix = ColorEnum::WHITE === $this->getColor() ? '.' : '...';

        return null !== $moveNumber ? $moveNumber . $colorSuffix : '';
    }

    public function clearAllComments(): void
    {
        $this->clearComments();
        $this->clearVariationComments();
    }

    public function clearComments(): void
    {
        $this->setBeforeMoveComment(null);
        $this->setAfterMoveComment(null);
    }

    public function clearVariationComments(): void
    {
        foreach ($this->getVariations() as $variationLine) {
            foreach ($variationLine as $moveNode) {
                $moveNode->clearAllComments();
            }
        }
    }

    public function clearVariations(): void
    {
        $this->variations = [];
    }

    public function clearAll(): void
    {
        $this->clearComments();
        $this->clearVariations();
    }

    public function __clone(): void
    {
        $clonedVariations = [];
        foreach ($this->variations as $variationLine) {
            $clonedVariations[] = clone $variationLine;
        }
        $this->variations = $clonedVariations;
    }
}
