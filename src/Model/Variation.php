<?php

namespace Cmuset\PgnParser\Model;

use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Exporter\GameExporter;

/**
 * @implements \IteratorAggregate<string, MoveNode>
 * @implements \ArrayAccess<string, MoveNode>
 */
class Variation implements \IteratorAggregate, \ArrayAccess, \Countable
{
    private ?string $identifier = null;

    /** @var array<string, MoveNode> */
    private array $nodes = [];

    /**
     * @param string|MoveNode ...$nodes can be SAN strings or MoveNodes
     */
    public function __construct(string|MoveNode ...$nodes)
    {
        $this->addNodes(...$nodes);
    }

    public function getPGN(): string
    {
        return GameExporter::create()->export($this);
    }

    public function getLastMoveNode(): ?MoveNode
    {
        if (empty($this->nodes)) {
            return null;
        }

        return end($this->nodes);
    }

    /**
     * @param string|MoveNode $node can be SAN string or MoveNode
     */
    public function addNode(string|MoveNode $node): void
    {
        $node = is_string($node) ? new MoveNode($node) : $node;
        $lastMoveNode = $this->getLastMoveNode();

        if ($lastMoveNode?->getColor() && $node->getColor() && $node->getColor() === $lastMoveNode->getColor()) {
            $node->getMove()->setPiece($node->getMove()->getPiece()->opposite()); // Ensure correct color
        }

        if (null === $node->getMoveNumber()) {
            $node->setMoveNumber(null === $lastMoveNode ? 1
                : (ColorEnum::WHITE === $node->getColor()
                    ? $lastMoveNode->getMoveNumber() + 1
                    : $lastMoveNode->getMoveNumber())
            );
        }

        $key = $node->getMoveNumber() . (ColorEnum::BLACK === $node->getColor() ? '...' : '.');
        $this->identifier = $this->identifier ?: $node->getMove()?->getSAN() ?? null;
        $this->nodes[$key] = $node;
    }

    public function addNodes(string|MoveNode ...$moveNodes): void
    {
        foreach ($moveNodes as $moveNode) {
            $this->addNode($moveNode);
        }
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->nodes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->nodes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->nodes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->nodes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->nodes[$offset]);
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function isEmpty(): bool
    {
        return empty($this->nodes);
    }

    public function __clone(): void
    {
        $clonedNodes = [];
        foreach ($this->nodes as $key => $node) {
            $clonedNodes[$key] = clone $node;
        }
        $this->nodes = $clonedNodes;
    }
}
