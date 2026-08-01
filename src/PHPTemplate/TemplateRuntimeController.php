<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use ReflectionMethod;

class TemplateRuntimeController {

	use ExecInClass;

	public static function getConstructorTestArgs(\PHPUnit\Framework\TestCase $tc): array {
		$tc_createStub = new ReflectionMethod($tc::class, 'createStub');
		return [
			'renderer' => $tc_createStub->invoke($tc, Renderer::class),
			'template' => $tc_createStub->invoke($tc, TemplateBase::class),
		];
	}
	public function __construct(
		public readonly Renderer $renderer,
		protected readonly TemplateBase $template,
	) {}

	public function tpl(string $template): RenderObjects\TemplateRenderProxy|RenderObjects\Error {
		try {
			return $this->renderer->getTemplateAsProxy($template);
		} catch (Exceptions\TemplateNotFoundException $e) {
			return new RenderObjects\Error($this->renderer, $e);
		}
	}
}
