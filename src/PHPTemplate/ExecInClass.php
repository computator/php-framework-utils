<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Closure;

trait ExecInClass {
	private array $__exec_data;
	final public function __execInClass(Closure $func, array $exec_data): mixed {
		$this->__exec_data = $exec_data;
		return $func->call($this);
	}
}
