<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Closure;

abstract class TemplateBase {
	abstract public function get_contents(int $offset = 0, ?int $length = null): string;
	abstract public function execute(array $context, mixed ...$controller_args): mixed;

	protected static function run_in_controller_exec(Closure $func, array $func_data, array $context, mixed ...$controller_args): mixed {
		return (new class ($func, $func_data, $context, ...$controller_args) extends TemplateRuntimeController {
			private readonly bool $__run;
			final public function __construct(
				private readonly Closure $__func,
				private readonly array $__func_data,
				private array $__context,
				mixed ...$controller_args,
			) {
				parent::__construct(...$controller_args);
			}

			final public function __exec(): mixed {
				if (isset($this->__run))
					throw new \LogicException("'" . __METHOD__ . "' is internal only");
				$this->__run = true;
				return $this->__func->call($this);
			}
		})->__exec();
	}
}
