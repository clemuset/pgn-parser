<?php

namespace Cmuset\PgnParser\Validator;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Validator\Model\GameViolation;

interface GameValidatorInterface
{
    public function validate(Game $game): ?GameViolation;
}
