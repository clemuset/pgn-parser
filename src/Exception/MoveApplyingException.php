<?php

namespace Cmuset\PgnParser\Exception;

use Cmuset\PgnParser\Enum\Violation\MoveViolationEnum;
use Cmuset\PgnParser\Enum\Violation\PositionViolationEnum;

class MoveApplyingException extends \Exception
{
    public function __construct(
        private readonly ?MoveViolationEnum $moveViolation = null,
        /** @var PositionViolationEnum[] $positionViolations */
        private readonly array $positionViolations = []
    ) {
        if (MoveViolationEnum::NEXT_POSITION_INVALID === $moveViolation) {
            $message = 'The move results in an invalid position. Violations: ' . implode(', ', array_map(fn (PositionViolationEnum $v) => $v->value, $this->positionViolations));
            parent::__construct($message);
        } else {
            parent::__construct($moveViolation->value ?? 'An error occurred while applying the move.');
        }
    }

    public function getMoveViolation(): ?MoveViolationEnum
    {
        return $this->moveViolation;
    }
}
