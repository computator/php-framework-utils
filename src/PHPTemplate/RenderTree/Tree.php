<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use WeakMap;
use ValueError;

use function get_class;

class Tree {
	public readonly Node $root;
	protected Node $current_node;
	protected WeakMap $in_tree_weakmap;

	/**
	 * @param callable(Node $node): mixed|false $callback
	 *     return false from the callback to abort walking the rest of the tree
	 * @param ?callable(Node $node): bool $filter
	 *     return false from the optional filter callback to skip the node and it's children
	 */
	public static function walk(Node $start, callable $callback, ?callable $filter = null): bool {
		if ($filter && !$filter($start))
			return true;
		if ($callback($start) === false)
			return false;
		if (!$start->isLeaf()) {
			foreach ($start as $n) {
				if ($filter && !$filter($start))
					continue;
				if (static::walk($n, $callback, $filter) === false)
					return false;
			}
		}
		return true;
	}

	/** @return array<array|Renderable|Null>|Renderable|null */
	public static function map_structure_values(Node $start): array|Renderable|null {
		if ($start->isLeaf())
			return $start->getValue();
		return array_map(static::map_structure_values(...), [...$start]);
	}

	/** @return array<array|string>|string */
	public static function map_structure_types(Node $start): array|string {
		if ($start->isLeaf())
			return get_class($start);
		return array_map(
			fn ($n) => [
				get_class($n) => $n->isLeaf()
					? (($v = $n->getValue()) ? get_class($v) : null)
					: static::map_structure_types($n),
			],
			[...$start],
		);
	}

	public function __construct(Node $root_node) {
		$this->root = $root_node;
		$this->current_node = $this->root;

		$this->in_tree_weakmap = new WeakMap();
		static::walk($this->root, fn (Node $n) => $this->in_tree_weakmap[$n] = true);
	}

	public function isEmpty(): bool {
		return $this->root->isLeaf();
	}

	public function containsNode(Node $node): bool {
		if (isset($this->in_tree_weakmap[$node]))
			return true;
		$found = false;
		static::walk($this->root, function ($curr) use ($node, &$found) {
			$this->in_tree_weakmap[$curr] = true;
			if ($curr === $node) {
				$found = true;
				return false;
			}
		});
		return $found;
	}

	public function getCurrentNode(): Node {
		return $this->current_node;
	}

	public function setCurrentNode(Node $node): void {
		if (!$this->containsNode($node))
			throw new ValueError("node not found in tree");
		$this->current_node = $node;
	}

	public function addNode(?Node $node = null): Node {
		if ($node === null)
			$node = Node::withValue(null);
		elseif ($this->containsNode($node))
			throw new ValueError("node cannot be added to tree in more than one place");
		$this->current_node->appendChildren($node);
		if (!$node->isLeaf())
			static::walk($node, fn($n) => $this->in_tree_weakmap[$n] = true);
		else
			$this->in_tree_weakmap[$node] = true;
		return $node;
	}

	public function addValue(Renderable $value): void {
		if (!$this->isEmpty() && $this->current_node->isLeaf() && !$this->current_node->hasValue())
			$this->current_node->setValue($value);
		else
			$this->current_node->appendChildren(Node::withValue($value));
	}

	public function render(): void {
		static::walk($this->root,
			fn ($n) => ($n->isLeaf() && $n->getValue()?->render()) || true,
			fn ($n) => !($n instanceof IgnoredNode),
		);
	}
}
