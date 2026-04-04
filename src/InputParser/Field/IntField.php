<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser\Field;

use function is_int, is_numeric;

class IntField extends Base {
	public function parse(mixed $value): int {
		if (is_int($value))
			return $value;
		if (!is_numeric($value))
			throw new \ValueError("value is not a numeric string");
		$i = $value + 0; // cast to int or float
		if (!is_int($i))
			throw new \ValueError("parsed value does not result in an integer");
		return $i;
	}
}
