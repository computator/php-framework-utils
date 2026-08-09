<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use WeakMap;
use ValueError;

class Tree {
	protected readonly Node $root;
	protected Node $current_node;
	protected WeakMap $in_tree_weakmap;

	/**
	 * @param callable(Node $node): mixed|false $callback
	 *     return false from the callback to abort walking the rest of the tree
	 */
	public static function walk(Node $start, callable $callback): bool {
		if ($callback($start) === false)
			return false;
		if (!$start->isLeaf()) {
			foreach ($start as $n) {
				if (static::walk($n, $callback) === false)
					return false;
			}
		}
		return true;
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
}
