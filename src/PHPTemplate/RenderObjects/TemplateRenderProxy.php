<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderObjects;

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\Templates;

class TemplateRenderProxy {
	protected static int $next_id = 1;
	public readonly int $id;
	public function __construct(
		protected readonly Renderer $renderer,
		protected readonly Templates\Base $tpl,
	) {
		$this->id = self::$next_id++;
	}

	public function __invoke(): void {
		$this->renderer->renderChild($this);
	}
}
