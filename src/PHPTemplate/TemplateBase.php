<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Closure;

abstract class TemplateBase {
	abstract public function get_contents(int $offset = 0, ?int $length = null): string;
	abstract public function execute(array $context, mixed ...$controller_args): mixed;
}
