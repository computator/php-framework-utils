<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser\Field;

class StringField extends Base {
	public function parse(mixed $value): string {
		return (string) $value;
	}
}
