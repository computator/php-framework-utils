<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderObjects;

use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateRenderProxy::class)]
final class TemplateRenderProxyTest extends TestCase {
	public function testInstanceIdsAreUnique(): void {
		$r = $this->createStub(RenderManager::class);
		$t = $this->createStub(Templates\Base::class);

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
		$r = $this->createMock(RenderManager::class);
		$proxy = new TemplateRenderProxy($r, $this->createStub(Templates\Base::class));

		$r
			->expects($this->once())
			->method('renderChild')
			->with($proxy);

		$proxy();
	}
}
