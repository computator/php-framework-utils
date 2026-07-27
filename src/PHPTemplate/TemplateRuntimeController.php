<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TemplateRuntimeController {
	public function __construct(
		public readonly Renderer $renderer,
		public readonly TemplateBase $tpl,
	) {}
}
