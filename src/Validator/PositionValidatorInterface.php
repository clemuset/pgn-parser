<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Validator\Enum\PositionViolationEnum;

interface PositionValidatorInterface
{
    /**
     * @return PositionViolationEnum[]
     */
    public function validate(Position $position): array;
}
