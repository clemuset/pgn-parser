<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Exporter\GameExporter;
use Cmuset\PgnParser\Parser\PGNParser;

class Game
{
    /** @var array<string,string> */
    private array $tags = [];
    private Position $initialPosition;
    /** @var array<MoveNode> */
    private array $mainLine;
    private ?ResultEnum $result = null;

    public static function fromPGN(string $pgn): self
    {
        return PGNParser::create()->parse($pgn);
    }

    public function getPGN(): string
    {
        return GameExporter::create()->export($this);
    }

    public function getInitialPosition(): Position
    {
        return $this->initialPosition;
    }

    public function setInitialPosition(Position $initialPosition): void
    {
        $this->initialPosition = $initialPosition;
    }

    /** @return array<MoveNode> */
    public function getMainLine(): array
    {
        return $this->mainLine;
    }

    /** @param array<MoveNode> $mainLine */
    public function setMainLine(array $mainLine): void
    {
        $this->mainLine = $mainLine;
    }

    public function addMoveNode(MoveNode $moveNode): void
    {
        $key = $moveNode->getMoveNumber() . (ColorEnum::WHITE === $moveNode->getColor() ? '.' : '...');
        $this->mainLine[$key] = $moveNode;
    }

    public function addMoveNodes(MoveNode ...$moveNodes): void
    {
        foreach ($moveNodes as $moveNode) {
            $this->addMoveNode($moveNode);
        }
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setTag(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    public function getTag(string $key): ?string
    {
        return $this->tags[$key] ?? null;
    }

    public function removeTag(string $key): void
    {
        unset($this->tags[$key]);
    }

    public function getResult(): ?ResultEnum
    {
        return $this->result;
    }

    public function setResult(?ResultEnum $result): void
    {
        $this->result = $result;
    }
}
