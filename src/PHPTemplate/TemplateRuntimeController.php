<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;

use ReflectionMethod;

class TemplateRuntimeController {

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
		} catch (Exceptions\RendererStateException $e) {
			throw new Exceptions\TemplateLogicException("inherit can not be called more than once", previous: $e);
		} catch (Exceptions\RendererException $e) {
			throw new Exceptions\TemplateRenderException("renderer error: $e", previous: $e);
		}
	}

	public function define(string $block_name): void {
		try {
			$this->renderer->startRenderingBlock($this->template, $block_name);
		} catch (Exceptions\RendererStateException $e) {
			throw new Exceptions\TemplateLogicException("can't start a new block definition: $e", previous: $e);
		} catch (Exceptions\RendererException $e) {
			throw new Exceptions\TemplateRenderException("renderer error: $e", previous: $e);
		}
	}

	public function define_end(): void {
		try {
			$this->renderer->endRenderingBlock($this->template);
		} catch (Exceptions\RendererStateException $e) {
			throw new Exceptions\TemplateLogicException("can't end the current block: $e", previous: $e);
		} catch (Exceptions\RendererException $e) {
			throw new Exceptions\TemplateRenderException("renderer error: $e", previous: $e);
		}
	}
}
