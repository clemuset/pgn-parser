<?php

namespace Cmuset\PgnParser\Splitter;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;

class VariationSplitter implements VariationSplitterInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @return Variation[]
     */
    public function split(Game|Variation $variation): array
    {
        if ($variation instanceof Game) {
            $variation = $variation->getMainLine();
        }

        // Variation itself + all its extracted variations
        $variations = array_merge([clone $variation], $this->extractAllVariations($variation));

        // Clear variations from all sub variations
        array_map(fn (Variation $variation) => $variation->clearVariations(), $variations);

        return $this->variationArrayUnique($variations);
    }

    /**
     * @return Variation[]
     */
    private function extractAllVariations(Variation $variation): array
    {
        $allVariations = [];

        $moves = [];
        /** @var MoveNode $node */
        foreach ($variation as $node) {
            foreach ($node->getVariations() as $subVariation) {
                $cleanedVariation = $this->cloneVariation($subVariation, $moves);
                $allVariations[] = $cleanedVariation;

                $nestedVariations = $this->extractAllVariations($cleanedVariation);
                $allVariations = array_merge($allVariations, $nestedVariations);
            }

            $moves[] = clone $node;
        }

        return $allVariations;
    }

    private function cloneVariation(Variation $variation, array $previousMoves = []): Variation
    {
        $clonedVariation = new Variation(...$previousMoves);

        /** @var MoveNode $node */
        foreach ($variation as $node) {
            $clonedNode = clone $node;
            $clonedVariation->addNode($clonedNode);
        }

        return $clonedVariation;
    }

    /**
     * @param Variation[] $variations
     *
     * @return Variation[]
     */
    private function variationArrayUnique(array $variations): array
    {
        $result = [];
        foreach ($variations as $variation) {
            $result[$variation->getLitePGN()] = $variation;
        }

        return array_values($result);
    }
}
