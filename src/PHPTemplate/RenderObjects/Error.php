<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderObjects;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\Renderer;

class Error {
	public function __construct(
		protected readonly Renderer $renderer,
		public readonly Exceptions\TemplateRenderException $exception,
	) {}

	public function __call(string $method, $args): self {
		return $this;
	}

	public function __invoke(): void {
		$this->renderer->renderError($this->exception->getMessage());
	}
}
