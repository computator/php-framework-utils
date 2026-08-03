<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\Templates;

abstract class Base {
	abstract public function get_contents(int $offset = 0, ?int $length = null): string;
	abstract public function execute(array $context, mixed ...$controller_args): mixed;
}
