<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

abstract class TemplateBase {
	abstract public function get_contents(int $offset = 0, ?int $length = null): string;
	abstract public function execute(mixed ...$__context): mixed;
}
