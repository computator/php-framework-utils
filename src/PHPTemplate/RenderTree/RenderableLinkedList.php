<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use ArrayAccess;
use Countable;
use Iterator;

interface RenderableLinkedList extends Iterator, Countable, ArrayAccess {
	public function add(int $index, Renderable $value): void;
	/** @return Renderable */
	public function bottom(): mixed;
	/** @return Renderable */
	public function current(): mixed;
	public function getIteratorMode(): int;
	public function isEmpty(): bool;
	public function key(): int;
	/** @param int $index */
	public function offsetExists($index): bool;
	/**
	 * @param int $index
	 * @return Renderable
	 */
	public function offsetGet($index): mixed;
	/**
	 * @param int $index
	 * @param Renderable $value
	 */
	public function offsetSet($index, $value): void;
	/** @param int $index */
	public function offsetUnset($index): void;
	/** @return Renderable */
	public function pop(): mixed;
	public function prev(): void;
	public function push(Renderable $value): void;
	/** @return Renderable */
	public function shift(): mixed;
	/** @return Renderable */
	public function top(): mixed;
	public function unshift(Renderable $value): void;
}
