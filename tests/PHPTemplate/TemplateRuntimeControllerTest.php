<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;
use Computator\FrameworkUtils\PHPTemplate\Templates;
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
		$r = $this->createMock(RenderManager::class);
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
		$e = new Exceptions\TemplateNotFoundException();
		$r = $this->createMock(RenderManager::class);
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

	public function testInheritSetsParent(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('setParentForTemplate')
			->with($t, 'test_tpl');
		(new TemplateRuntimeController(
			$r,
			$t,
		))->inherit('test_tpl');
	}

	public function testMultipleInheritCallsThrow(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->exactly(2))
			->method('setParentForTemplate')
			->willReturnCallback(function ($t_arg, $n_arg) use (&$t) {
				static $call = 1;
				$this->assertSame($t, $t_arg);
				if ($call++ == 1) {
					$this->assertSame('test_tpl', $n_arg);
				} else {
					$this->assertSame('test_tpl2', $n_arg);
					throw new Exceptions\RendererException();
				}
			});
		$c = new TemplateRuntimeController(
			$r,
			$t,
		);
		$c->inherit('test_tpl');
		$this->expectException(Exceptions\TemplateRenderException::class);
		$c->inherit('test_tpl2');
	}
}
