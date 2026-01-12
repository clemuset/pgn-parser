<?php

namespace Cmuset\PgnParser\Splitter;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Variation;

class VariationSplitter implements VariationSplitterInterface
{
    public function __construct(
        private ?SplitOptions $options = null,
    ) {
        if (null === $this->options) {
            $this->options = new SplitOptions();
        }
    }

    public static function create(?SplitOptions $options = null): self
    {
        return new self($options);
    }

    /**
     * @return Variation[]
     */
    public function split(Game|Variation $variation, ?SplitOptions $options = null): array
    {
        if (null !== $options) {
            $this->options = $options;
        }

        if ($variation instanceof Game) {
            $variation = $variation->getMainLine();
        }

        // Variation itself + all its extracted variations
        $variations = array_merge([clone $variation], $this->extractAllVariations($variation));

        // Clear variations from all sub variations
        array_map(fn (Variation $variation) => $variation->clearVariations($this->options->colorToSplit), $variations);

        return $variations;
    }

    /**
     * @return Variation[]
     */
    private function extractAllVariations(Variation $variation): array
    {
        $allVariations = [];

        /** @var MoveNode $node */
        foreach ($variation as $node) {
            if (null !== $this->options->colorToSplit && $node->getColor() !== $this->options->colorToSplit) {
                continue;
            }

            foreach ($node->getVariations() as $subVariation) {
                $cleanedVariation = $this->cloneVariation($subVariation, $variation);
                $allVariations[] = $cleanedVariation;

                $nestedVariations = $this->extractAllVariations($cleanedVariation);
                $allVariations = array_merge($allVariations, $nestedVariations);
            }
        }

        return $allVariations;
    }

    private function cloneVariation(Variation $variation, ?Variation $parent = null): Variation
    {
        if ($this->options->keepPreviousMoves && null !== $parent) {
            $clonedVariation = clone $parent;
            $clonedVariation->removeNodesFrom($variation->getFirstNode()->getKey());
        } else {
            $clonedVariation = new Variation();
        }

        /** @var MoveNode $node */
        foreach ($variation as $node) {
            $clonedNode = clone $node;
            $clonedVariation->addNode($clonedNode);
        }

        return $clonedVariation;
    }
}
