<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\TemplateBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateRenderProxy::class)]
final class TemplateRenderProxyTest extends TestCase {
	public function testInstanceIdsAreUnique(): void {
		$r = $this->createStub(Renderer::class);
		$t = $this->createStub(TemplateBase::class);

		$p1 = new TemplateRenderProxy($r, $t);
		$p2 = new TemplateRenderProxy($r, $t);
		$p3 = new TemplateRenderProxy($r, $t);

		$this->assertCount(3, array_unique([
			$p1->id,
			$p2->id,
			$p3->id,
		], SORT_REGULAR));
	}

	public function testInvokeRendersSelf(): void {
		$r = $this->createMock(Renderer::class);
		$proxy = new TemplateRenderProxy($r, $this->createStub(TemplateBase::class));

		$r
			->expects($this->once())
			->method('renderChild')
			->with($proxy);

		$proxy();
	}
}
