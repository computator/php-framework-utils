<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\Templates;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;

class File extends Base {
	public readonly string $path;

	public function __construct(string $path) {
		if ($path == '' || !is_readable($path))
			throw new Exceptions\TemplateNotFoundException("template '{$path}' does not exist or is not readable");
		$this->path = $path;
	}

	public function get_contents(int $offset = 0, ?int $length = null): string {
		$rv = @file_get_contents($this->path, offset: $offset, length: $length);
		if ($rv === false)
			throw new Exceptions\TemplateRenderException("error reading template file '{$this->path}'");
		return $rv;
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		$rc = new TemplateRuntimeController(...$controller_args);
		return (function (...$__exec_args) {
			extract($__exec_args['context']);
			unset($__exec_args['context']);
			return include $__exec_args['path'];
		})->call($rc, context: $context, path: $this->path);
	}
}
