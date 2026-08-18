<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use function is_string;

class TreeUtils {
	public static function map_tree_struct_parents(array $tree): array {
		return array_map(function ($n) {
			foreach ($n as $k => $v) {
				return [
					get_parent_class($k) => is_string($v)
						? (get_parent_class($v) ?: (str_starts_with($v, 'MockObject_') ? explode('_', $v, 3)[1] : $v))
						: self::map_tree_struct_parents($v),
				];
			}
		}, $tree);
	}
}
