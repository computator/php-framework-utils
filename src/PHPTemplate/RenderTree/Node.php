<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use ArgumentCountError;
use Iterator;
use IteratorAggregate;

use LogicException;
use function count;

class Node implements IteratorAggregate {
	protected NodeLinkedList $children;
	protected ?Renderable $value = null;

	public static function withValue(?Renderable $value): self {
		$n = new self();
		$n->value = $value;
		return $n;
	}

	public static function withChildren(self ...$children): self {
		if (!count($children))
			throw new ArgumentCountError("at least one parameter is required");
		$n = new self();
		$n->transform();
		array_walk($children, fn ($c) => $n->children->push($c));
		return $n;
	}

	private function __construct() {}

	public function getIterator(): Iterator {
		return $this->children ?? [];
	}

	public function getValue(): ?Renderable {
		if (!$this->isLeaf())
			throw new LogicException(__METHOD__." is only valid on leaf nodes");
		return $this->value;
	}

	public function hasValue(): bool {
		if (!$this->isLeaf())
			throw new LogicException(__METHOD__." is only valid on leaf nodes");
		return $this->value !== null;
	}

	public function setValue(Renderable $value): ?Renderable {
		if (!$this->isLeaf())
			throw new LogicException(__METHOD__." is only valid on leaf nodes");
		$oldval = $this->value;
		$this->value = $value;
		return $oldval;
	}

	protected function transform() {
		assert(!isset($this->children));
		$this->children = new NodeLinkedListImpl();
		if ($this->value !== null) {
			$this->children->push(self::withValue($this->value));
			$this->value = null;
		}
	}

	public function isLeaf(): bool {
		return !isset($this->children) || $this->children->isEmpty();
	}

	public function appendChildren(self ...$nodes): void {
		if (!isset($this->children))
			$this->transform();
		array_walk($nodes, fn ($c) => $this->children->push($c));
	}
}
