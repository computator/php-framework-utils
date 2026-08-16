<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;

class WalkCountingTree extends Tree {
	private static int $walk_calls = 0;
	private static bool $walking = false;

	public static function walk(Node $start, callable $callback, ?callable $filter = null): bool {
		if (self::$walking)
			return parent::walk($start, $callback, $filter);
		self::$walk_calls++;
		self::$walking = true;
		$rv = parent::walk($start, $callback, $filter);
		self::$walking = false;
		return $rv;
	}

	public static function getCalls(): int {
		return self::$walk_calls;
	}

	public static function resetCalls(): void {
		self::$walk_calls = 0;
	}
}
