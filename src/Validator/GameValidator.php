<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Enum\Violation\ViolationEnumInterface;
use Cmuset\PgnParser\Exception\MoveApplyingException;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\MoveApplier\MoveApplier;

class GameValidator
{
    private readonly MoveApplier $moveApplier;

    public function __construct()
    {
        $this->moveApplier = new MoveApplier();
    }

    /**
     * @return ViolationEnumInterface[]
     */
    public function validate(Game $game): array
    {
        return $this->validateLine($game->getInitialPosition(), $game->getMainLine());
    }

    /**
     * @param MoveNode[] $line
     *
     * @return ViolationEnumInterface[]
     */
    private function validateLine(Position $position, array $line): array
    {
        $currentPosition = clone $position;
        foreach ($line as $node) {
            foreach ($node->getVariations() as $variation) {
                $violations = $this->validateLine($currentPosition, $variation);

                if (count($violations) > 0) {
                    return $violations;
                }
            }

            try {
                $currentPosition = $this->moveApplier->apply($currentPosition, $node->getMove());
            } catch (MoveApplyingException $e) {
                return [$e->getMoveViolation(), ...$e->getPositionViolations()];
            }
        }

        return [];
    }
}
