<?php

namespace Cmuset\PgnParser\Validator\Model;

use Cmuset\PgnParser\Validator\Enum\MoveViolationEnum;
use Cmuset\PgnParser\Validator\Enum\PositionViolationEnum;

class GameViolation
{
    public function __construct(
        private readonly string $path,
        private readonly MoveViolationEnum $moveViolation,
        /** @var PositionViolationEnum[] */
        private readonly array $positionViolations = []
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMoveViolation(): MoveViolationEnum
    {
        return $this->moveViolation;
    }

    /**
     * @return PositionViolationEnum[]
     */
    public function getPositionViolations(): array
    {
        return $this->positionViolations;
    }
}
