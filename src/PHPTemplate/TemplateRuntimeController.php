<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TemplateRuntimeController {

	public static function getConstructorTestArgs(\PHPUnit\Framework\TestCase $tc): array {
		return (fn () => [
			'renderer' => $this->createStub(Renderer::class),
			'template' => $this->createStub(TemplateBase::class),
		])->call($tc);
	}
	public function __construct(
		public readonly Renderer $renderer,
		protected readonly TemplateBase $template,
	) {}

	public function tpl(string $template): ?TemplateRenderProxy {
		return $this->renderer->getTemplateAsProxy($template);
	}
}
