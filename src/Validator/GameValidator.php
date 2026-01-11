<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveApplier;
use Cmuset\PgnParser\Validator\Model\GameViolation;

class GameValidator
{
    private readonly MoveApplier $moveApplier;

    public function __construct()
    {
        $this->moveApplier = new MoveApplier();
    }

    public function validate(Game $game): ?GameViolation
    {
        return $this->validateLine($game->getInitialPosition(), $game->getMainLine());
    }

    /**
     * @param MoveNode[] $line
     */
    private function validateLine(Position $position, array $line, string $movePathPrefix = ''): ?GameViolation
    {
        $currentPosition = clone $position;
        foreach ($line as $node) {
            $movePath = $movePathPrefix . $node->getKey();
            foreach ($node->getVariations() as $variationKey => $variation) {
                $violation = $this->validateLine($currentPosition, $variation, $movePath . '[' . $variationKey . ']');

                if (null !== $violation) {
                    return $violation;
                }
            }

            try {
                $currentPosition = $this->moveApplier->apply($currentPosition, $node->getMove());
            } catch (MoveApplyingException $e) {
                return new GameViolation(
                    $movePath,
                    $e->getMoveViolation(),
                    $e->getPositionViolations()
                );
            }
        }

        return null;
    }
}
