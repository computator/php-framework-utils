<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use ArrayAccess;
use Countable;
use Iterator;

interface NodeLinkedList extends Iterator, Countable, ArrayAccess {
	public function add(int $index, Node $value): void;
	/** @return Node */
	public function bottom(): mixed;
	/** @return Node */
	public function current(): mixed;
	public function getIteratorMode(): int;
	public function isEmpty(): bool;
	public function key(): int;
	/** @param int $index */
	public function offsetExists($index): bool;
	/**
	 * @param int $index
	 * @return Node
	 */
	public function offsetGet($index): mixed;
	/**
	 * @param int $index
	 * @param Node $value
	 */
	public function offsetSet($index, $value): void;
	/** @param int $index */
	public function offsetUnset($index): void;
	/** @return Node */
	public function pop(): mixed;
	public function prev(): void;
	public function push(Node $value): void;
	/** @return Node */
	public function shift(): mixed;
	/** @return Node */
	public function top(): mixed;
	public function unshift(Node $value): void;
}
