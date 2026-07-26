<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use RuntimeException;

class FileTemplate extends TemplateBase {
	public readonly string $path;

	public function __construct(string $path) {
		if (!is_readable($path))
			throw new RuntimeException("template '{$path}' does not exist or is not readable");
		$this->path = $path;
	}

	protected function get_contents(int $offset = 0, ?int $length = null): string {
		$rv = file_get_contents($this->path, offset: $offset, length: $length);
		if ($rv === false)
			throw new RuntimeException("error reading template file '{$this->path}'");
		return $rv;
	}

	public function execute(array $__context): mixed {
		extract($__context);
		return include $this->path;
	}
}
