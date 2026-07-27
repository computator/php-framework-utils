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

	public function get_contents(int $offset = 0, ?int $length = null): string {
		$rv = file_get_contents($this->path, offset: $offset, length: $length);
		if ($rv === false)
			throw new RuntimeException("error reading template file '{$this->path}'");
		return $rv;
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		$rc = new TemplateRuntimeController(...$controller_args);
		return $rc->__execInClass(function () {
			extract($this->__exec_data['context']);
			unset($this->__exec_data['context']);
			return include $this->__exec_data['path'];
		}, ['context' => $context, 'path' => $this->path]);
	}
}
