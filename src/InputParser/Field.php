<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser;

class Field {
	public static function __callStatic(string $name, array $args): Field\Base {
		$c = __NAMESPACE__ . "\\Field\\{$name}";
		// try NameField if Name doesn't exist
		if (!class_exists($c))
			$c .= 'Field';
		return new $c(...$args);
	}
}
