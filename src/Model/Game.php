<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Exporter\GameExporter;
use Cmuset\PgnParser\Parser\PGNParser;

class Game
{
    /** @var array<string,string> */
    private array $tags = [];
    private ?Position $initialPosition = null;
    private Variation $mainLine;
    private ?ResultEnum $result = null;

    public function __construct()
    {
        $this->mainLine = new Variation();
    }

    public static function fromPGN(string $pgn): self
    {
        return PGNParser::create()->parse($pgn);
    }

    public function getPGN(): string
    {
        return GameExporter::create()->export($this);
    }

    public function getLitePGN(): string
    {
        $clonedGame = clone $this;
        $clonedGame->clearAllComments();
        $clonedGame->tags = [];

        return GameExporter::create()->export($clonedGame);
    }

    public function getInitialPosition(): Position
    {
        return $this->initialPosition;
    }

    public function setInitialPosition(Position $initialPosition): void
    {
        $this->initialPosition = $initialPosition;
    }

    public function getMainLine(): Variation
    {
        return $this->mainLine;
    }

    public function setMainLine(Variation $mainLine): void
    {
        $this->mainLine = $mainLine;
    }

    public function getLastMoveNode(): ?MoveNode
    {
        return $this->mainLine->getLastMoveNode();
    }

    /**
     * @param string|MoveNode $moveNode can be a SAN string or a MoveNode object
     */
    public function addMoveNode(string|MoveNode $moveNode): void
    {
        $this->mainLine->addNode(is_string($moveNode) ? new MoveNode($moveNode) : $moveNode);
    }

    /**
     * @param string|MoveNode ...$moveNodes can be SAN strings or MoveNode objects
     */
    public function addMoveNodes(string|MoveNode ...$moveNodes): void
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

    public function clearAllComments(): void
    {
        foreach ($this->mainLine as $moveNode) {
            $moveNode->clearAllComments();
        }
    }

    public function __clone(): void
    {
        $this->mainLine = clone $this->mainLine;
        $this->initialPosition = $this->initialPosition ? clone $this->initialPosition : null;
    }
}
