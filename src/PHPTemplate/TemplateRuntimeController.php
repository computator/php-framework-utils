<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;

use ReflectionMethod;

class TemplateRuntimeController {

	use ExecInClass;

	public static function getConstructorTestArgs(\PHPUnit\Framework\TestCase $tc): array {
		$tc_createStub = new ReflectionMethod($tc::class, 'createStub');
		return [
			'renderer' => $tc_createStub->invoke($tc, RenderManager::class),
			'template' => $tc_createStub->invoke($tc, Templates\Base::class),
		];
	}
	public function __construct(
		public readonly RenderManager $renderer,
		protected readonly Templates\Base $template,
	) {}

	public function tpl(string $template): RenderObjects\TemplateRenderProxy|RenderObjects\Error {
		try {
			return $this->renderer->getTemplateAsProxy($template);
		} catch (Exceptions\TemplateNotFoundException $e) {
			return new RenderObjects\Error($this->renderer, $e);
		}
	}

	public function inherit(string $parent_template): void {
		try {
			$this->renderer->setParentForTemplate($this->template, $parent_template);
		} catch (Exceptions\RendererException $e) {
			throw new Exceptions\TemplateRenderException("inherit can not be called more than once", previous: $e);
		}
	}
}
