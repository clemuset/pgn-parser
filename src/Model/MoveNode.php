<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\CommentAnchorEnum;

class MoveNode
{
    private ?Move $move = null;
    private ?int $moveNumber = null;
    private ?ColorEnum $color = null;
    private ?string $afterMoveComment = null;
    private ?string $beforeMoveComment = null;

    /** @var int[] */
    private array $nags = [];

    /** @var array<array<MoveNode>> */
    private array $variations = [];

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

    public function getColor(): ?ColorEnum
    {
        return $this->color;
    }

    public function setColor(?ColorEnum $color): void
    {
        $this->color = $color;
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

    /** @return array<array<MoveNode>> */
    public function getVariations(): array
    {
        return $this->variations;
    }

    /** @param array<array<MoveNode>> $variations */
    public function setVariations(array $variations): void
    {
        $this->variations = $variations;
    }

    /** @param array<MoveNode> $variationLine */
    public function addVariation(array $variationLine): void
    {
        $this->variations[] = $variationLine;
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

    public function __clone(): void
    {
        $clonedVariations = [];
        foreach ($this->variations as $variationLine) {
            $clonedLine = [];
            foreach ($variationLine as $moveNode) {
                $clonedLine[] = clone $moveNode;
            }
            $clonedVariations[] = $clonedLine;
        }
        $this->variations = $clonedVariations;
    }
}
