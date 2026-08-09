<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use ArgumentCountError;
use Iterator;
use IteratorAggregate;

use function count;

class Node implements IteratorAggregate {
	protected RenderableLinkedList $children;
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
		return $this->value;
	}

	protected function transform() {
		assert(!isset($this->children));
		$this->children = new RenderableLinkedListImpl();
		if ($this->value !== null) {
			$this->children->push($this->value);
			$this->value = null;
		}
	}

	public function isLeaf(): bool {
		return !isset($this->children) || $this->children->isEmpty();
	}
}
