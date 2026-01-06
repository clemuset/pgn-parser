<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Enum\Violation\MoveViolationEnum;
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

    public function validate(Game $game): ?MoveViolationEnum
    {
        return $this->validateLine($game->getInitialPosition(), $game->getMainLine());
    }

    /**
     * @param MoveNode[] $line
     */
    private function validateLine(Position $position, array $line): ?MoveViolationEnum
    {
        $currentPosition = clone $position;
        foreach ($line as $node) {
            foreach ($node->getVariations() as $variation) {
                $violation = $this->validateLine($currentPosition, $variation);

                if (null !== $violation) {
                    return $violation;
                }
            }

            try {
                $currentPosition = $this->moveApplier->apply($position, $node->getMove());
            } catch (MoveApplyingException $e) {
                return $e->getMoveViolation();
            }
        }

        return null;
    }
}
