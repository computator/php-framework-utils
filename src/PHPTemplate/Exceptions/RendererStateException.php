<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\Exceptions;

use Throwable;

class RendererStateException extends RendererException {
	/** @codeCoverageIgnore */
	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
		parent::__construct(
			"invalid state: {$message}",
			$code,
			$previous,
		);
	}
}
