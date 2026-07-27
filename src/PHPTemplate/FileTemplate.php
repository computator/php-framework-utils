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
		return (new class ($context, $this->path, ...$controller_args) extends TemplateRuntimeController {
			private readonly bool $__run;
			final public function __construct(
				private array $__context,
				private readonly string $__path,
				mixed ...$controller_args,
			) {
				parent::__construct(...$controller_args);
			}

			final public function __exec(): mixed {
				if (isset($this->__run))
					throw new \LogicException("'" . __METHOD__ . "' is internal only");
				$this->__run = true;
				extract($this->__context);
				return include $this->__path;
			}
		})->__exec();
	}
}
