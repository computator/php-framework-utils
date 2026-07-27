<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TextTemplate extends TemplateBase {
	public readonly string $content;

	public function __construct(string $content) {
		$this->content = $content;
	}

	public function get_contents(int $offset = 0, ?int $length = null): string {
		return substr($this->content, $offset, $length);
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		return (new class ($context, $this->content, ...$controller_args) extends TemplateRuntimeController {
			private readonly bool $__run;
			final public function __construct(
				private array $__context,
				private readonly string $__tpl,
				mixed ...$controller_args,
			) {
				parent::__construct(...$controller_args);
			}

			final public function __exec(): mixed {
				if (isset($this->__run))
					throw new \LogicException("'" . __METHOD__ . "' is internal only");
				$this->__run = true;
				extract($this->__context);
				// ending tag added to switch to HTML mode instead of starting in PHP mode
				return eval("?>{$this->__tpl}");
			}
		})->__exec();
	}
}
