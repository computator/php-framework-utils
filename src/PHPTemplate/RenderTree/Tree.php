<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

class Tree {
	protected Node $root;

	public function __construct(Node $root_node) {
		$this->root = $root_node;
	}

	public function isEmpty(): bool {
		return $this->root->isLeaf();
	}
}
