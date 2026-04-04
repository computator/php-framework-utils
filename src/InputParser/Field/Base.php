<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser\Field;

abstract class Base {
	abstract public function parse(mixed $value): mixed;
}
