<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\RenderObjects;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Error::class)]
final class ErrorTest extends TestCase {
	public function testExceptionPropertyValue(): void {
		$exp = new Exceptions\TemplateRenderException('asdf');
		$err = new Error(
			$this->createStub(RenderManager::class),
			$exp,
		);
		$this->assertSame($exp, $err->exception);
		$this->assertEquals('asdf', $err->exception->getMessage());
	}

	public function testCallingMethodsReturnsSelf(): void {
		$e = new Error(
			$this->createStub(RenderManager::class),
			$this->createStub(Exceptions\TemplateRenderException::class),
		);
		$this->assertSame($e, $e->asdf());
		$this->assertSame($e, $e->blah(1, 2, '3'));
		$this->assertSame($e, $e->tpl());
	}

	public function testInvokeRendersError(): void {
		$r = $this->createMock(RenderManager::class);
		$r
			->expects($this->once())
			->method('renderError')
			->with('asdf');

		(new Error(
			$r,
			new Exceptions\TemplateRenderException('asdf'),
		))();
	}
}
