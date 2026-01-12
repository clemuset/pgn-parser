<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Variation;
use Cmuset\PgnParser\MoveApplier\Exception\MoveApplyingException;
use Cmuset\PgnParser\MoveApplier\MoveApplier;
use Cmuset\PgnParser\Validator\Model\GameViolation;

class GameValidator implements GameValidatorInterface
{
    private readonly MoveApplier $moveApplier;

    public function __construct()
    {
        $this->moveApplier = new MoveApplier();
    }

    public function validate(Game $game): ?GameViolation
    {
        return $this->validateLine($game->getInitialPosition(), $game->getMainLine(), new Variation());
    }

    private function validateLine(Position $position, Variation $line, Variation $variationPath): ?GameViolation
    {
        $currentPosition = clone $position;
        $nodeVariationPath = clone $variationPath;
        foreach ($line as $node) {
            foreach ($node->getVariations() as $variation) {
                $violation = $this->validateLine($currentPosition, $variation, $nodeVariationPath);

                if (null !== $violation) {
                    return $violation;
                }
            }

            $clearedNode = clone $node;
            $clearedNode->clearAll();
            $nodeVariationPath->addNode($clearedNode);

            try {
                $this->moveApplier->apply($currentPosition, $node->getMove());
            } catch (MoveApplyingException $e) {
                return new GameViolation(
                    $nodeVariationPath->getPGN(),
                    $e->getMoveViolation(),
                    $e->getPositionViolations()
                );
            }
        }

        return null;
    }
}
