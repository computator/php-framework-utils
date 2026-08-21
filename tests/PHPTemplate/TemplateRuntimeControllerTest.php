<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

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

	public function testTplSuccessReturnsTemplateRenderProxy(): void {
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

	public function testTplFailureReturnsError(): void {
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

	public function testInheritSuccess(): void {
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

	public function testInheritInvalidState(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('setParentForTemplate')
			->with($t, 'test_tpl')
			->willThrowException(new Exceptions\RendererStateException());
		$this->expectException(Exceptions\TemplateLogicException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->inherit('test_tpl');
	}

	public function testInheritFailure(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('setParentForTemplate')
			->with($t, 'test_tpl')
			->willThrowException(new Exceptions\RendererException());
		$this->expectException(Exceptions\TemplateRenderException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->inherit('test_tpl');
	}

	public function testDefineSuccess(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('startRenderingBlock')
			->with($t, 'test_block');
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define('test_block');
	}

	public function testDefineInvalidState(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('startRenderingBlock')
			->with($t, 'test_block')
			->willThrowException(new Exceptions\RendererStateException());
		$this->expectException(Exceptions\TemplateLogicException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define('test_block');
	}

	public function testDefineFailure(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('startRenderingBlock')
			->with($t, 'test_block')
			->willThrowException(new Exceptions\RendererException());
		$this->expectException(Exceptions\TemplateRenderException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define('test_block');
	}

	public function testDefineEndSuccess(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('endRenderingBlock')
			->with($t);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define_end();
	}

	public function testDefineEndInvalidState(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('endRenderingBlock')
			->with($t)
			->willThrowException(new Exceptions\RendererStateException());
		$this->expectException(Exceptions\TemplateLogicException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define_end();
	}

	public function testDefineEndFailure(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('endRenderingBlock')
			->with($t)
			->willThrowException(new Exceptions\RendererException());
		$this->expectException(Exceptions\TemplateRenderException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->define_end();
	}

	public function testBlockSuccessExisting(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('renderChildBlock')
			->with($t, 'test_block')
			->willReturn(true);
		$rv = (new TemplateRuntimeController(
			$r,
			$t,
		))->block('test_block');
		$this->assertTrue($rv);
	}

	public function testBlockSuccessMissing(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('renderChildBlock')
			->with($t, 'test_block')
			->willReturn(false);
		$rv = (new TemplateRuntimeController(
			$r,
			$t,
		))->block('test_block');
		$this->assertFalse($rv);
	}

	public function testBlockInvalidState(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('renderChildBlock')
			->with($t, 'test_block')
			->willThrowException(new Exceptions\RendererStateException());
		$this->expectException(Exceptions\TemplateLogicException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->block('test_block');
	}

	public function testBlockFailure(): void {
		$r = $this->createMock(RenderManager::class);
		$t = $this->createMock(Templates\Base::class);
		$r
			->expects($this->once())
			->method('renderChildBlock')
			->with($t, 'test_block')
			->willThrowException(new Exceptions\RendererException());
		$this->expectException(Exceptions\TemplateRenderException::class);
		(new TemplateRuntimeController(
			$r,
			$t,
		))->block('test_block');
	}

}
