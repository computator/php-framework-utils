<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test;

use Computator\FrameworkUtils\PHPTemplate\Exceptions\TemplateNotFoundException;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateRuntimeController::class)]
final class TemplateRuntimeControllerTest extends TestCase {
	#[DoesNotPerformAssertions()]
	public function testGetConstructorTestArgsReturnsAllArgs(): void {
		new TemplateRuntimeController(
			...TemplateRuntimeController::getConstructorTestArgs($this),
		);
	}

	public function testTplWithValidTemplateReturnsTemplateRenderProxy(): void {
		$p = $this->createStub(TemplateRenderProxy::class);
		$r = $this->createMock(Renderer::class);
		$r
			->expects($this->once())
			->method('getTemplateAsProxy')
			->with('test_tpl')
			->willReturn($p);
		$rv = (new TemplateRuntimeController(
			$r,
			...array_diff_key(TemplateRuntimeController::getConstructorTestArgs($this), array_flip(['renderer'])),
		))->tpl('test_tpl');
		$this->assertSame($p, $rv);
	}

	public function testTplWithInvalidTemplateReturnsError(): void {
		$e = new TemplateNotFoundException();
		$r = $this->createMock(Renderer::class);
		$r
			->expects($this->once())
			->method('getTemplateAsProxy')
			->with('test_tpl')
			->willThrowException($e);
		$rv = (new TemplateRuntimeController(
			$r,
			...array_diff_key(TemplateRuntimeController::getConstructorTestArgs($this), array_flip(['renderer'])),
		))->tpl('test_tpl');
		$this->assertInstanceOf(RenderObjects\Error::class, $rv);
		$this->assertSame($e, $rv->exception);
	}
}
