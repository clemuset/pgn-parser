<?php

namespace Cmuset\PgnParser\Merger;

use Cmuset\PgnParser\Model\Variation;

class VariationMerger implements VariationMergerInterface
{
    public static function create(): self
    {
        return new self();
    }

    public function merge(Variation $mainLine, Variation ...$variations): Variation
    {
        $variations = $this->splitAll($variations);

        foreach ($variations as $variation) {
            foreach ($variation as $key => $moveNode) {
                if (!($mainNode = $mainLine->getNode($key))) {
                    $mainLine->addNode(clone $moveNode);
                    continue;
                }

                $san = $moveNode->getMove()->getSAN();

                if ($mainNode->getMove()->getSAN() === $san) {
                    continue;
                }

                foreach ($mainNode->getVariations() as $subVariation) {
                    if ($subVariation->getIdentifier() === $san) {
                        $this->merge($subVariation, $variation->cloneFrom($key));
                        continue 3;
                    }
                }

                $mainNode->addVariation($variation->cloneFrom($key));
                continue 2;
            }
        }

        return $mainLine;
    }

    /**
     * @param Variation[] $variations
     *
     * @return Variation[]
     */
    private function splitAll(array $variations): array
    {
        $allSplitted = [];
        foreach ($variations as $variation) {
            $allSplitted = array_merge($allSplitted, $variation->split());
        }

        return $allSplitted;
    }
}
