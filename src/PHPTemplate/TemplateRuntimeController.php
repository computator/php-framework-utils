<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TemplateRuntimeController {

	public static function getConstructorTestArgs(\PHPUnit\Framework\TestCase $tc): array {
		return (fn () => [
			'renderer' => $this->createStub(Renderer::class),
			'tpl' => $this->createStub(TemplateBase::class),
		])->call($tc);
	}
	public function __construct(
		public readonly Renderer $renderer,
		public readonly TemplateBase $tpl,
	) {}
}
