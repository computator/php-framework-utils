<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderObjects;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderManager;

class Error {
	public function __construct(
		protected readonly RenderManager $renderer,
		public readonly Exceptions\TemplateRenderException $exception,
	) {}

	public function __call(string $method, $args): self {
		return $this;
	}

	public function __invoke(): void {
		$this->renderer->renderError($this->exception->getMessage());
	}
}
