<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\StringTemplate;
use Computator\FrameworkUtils\PHPTemplate\TemplateBase;
use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase {
	public function testRender(): void {
		$t = $this->createStub(TemplateBase::class);
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		$r = new Renderer($t);
		$this->expectOutputString('asdf');
		$r->render();
	}

	public function testRenderToString(): void {
		$t = $this->createStub(TemplateBase::class);
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		$r = new Renderer($t);
		$this->expectOutputString('');
		$rv = $r->renderToString();
		$this->assertSame('asdf', $rv);
	}

	public function testTemplateExecuteContext(): void {
		$r = null;
		$t = $this->createMock(TemplateBase::class);
		$t
			->expects($this->once())
			->method('execute')
			->with($this->callback(function (...$args) use (&$r, &$t): bool {
				$this->assertSame([
					[],
					'renderer' => $r,
					'template' => $t,
				], $args);
				return true;
			}));
		$r = new Renderer($t);
		$r->renderToString();
	}

	public function testGetTemplateAsProxyTemplateMatches(): void {
		$res = new class ([
			$t = $this->createStub(TemplateBase::class),
			$this->createStub(TemplateBase::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): TemplateBase {
				return array_shift($this->tpls);
			}
		};
		$r = new Renderer($this->createStub(TemplateBase::class), $res);
		$p = $r->getTemplateAsProxy('test_tpl');

		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p));
	}

	public function testGetTemplateAsProxyReturnsUnique(): void {
		$res = new class ([
			$this->createStub(TemplateBase::class),
			$this->createStub(TemplateBase::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): TemplateBase {
				return array_shift($this->tpls);
			}
		};
		$r = new Renderer($this->createStub(TemplateBase::class), $res);
		$p1 = $r->getTemplateAsProxy('test_tpl');
		$p2 = $r->getTemplateAsProxy('test_tpl');
		$this->assertNotSame($p1, $p2);
		$this->assertNotSame($p1->id, $p2->id);
		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertNotSame($tpl_prop->getValue($p1), $tpl_prop->getValue($p2));
	}

	public function testGetTemplateInstanceAsProxyByIdWithExistingId(): void {
		$res = new class ([
			$t = $this->createStub(TemplateBase::class),
			$this->createStub(TemplateBase::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): TemplateBase {
				return array_shift($this->tpls);
			}
		};
		$r = new Renderer($this->createStub(TemplateBase::class), $res);
		$p1 = $r->getTemplateAsProxy('test_tpl');
		$p2 = $r->getTemplateInstanceAsProxyById($p1->id);
		$this->assertNotSame($p1, $p2);
		$this->assertNotSame($p1->id, $p2->id);
		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p1));
		$this->assertSame($tpl_prop->getValue($p1), $tpl_prop->getValue($p2));
	}

	public function testGetTemplateInstanceAsProxyByIdWithUnknownId(): void {
		$r = new Renderer($this->createStub(TemplateBase::class));
		$p = $r->getTemplateInstanceAsProxyById(1234);
		$this->assertNull($p);
	}

	public function testRenderChild(): void {
		$res = new class ([
			$t = $this->createStub(TemplateBase::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): TemplateBase {
				return array_shift($this->tpls);
			}
		};
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		$r = new Renderer($this->createStub(TemplateBase::class), $res);
		$p = $r->getTemplateAsProxy('test_tpl');

		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p));

		$this->expectOutputString('asdf');
		$r->renderChild($p);
	}

	public function testRenderErrorWithStringTemplate(): void {
		$r = new Renderer($this->createStub(TemplateBase::class));
		$e = new StringTemplate('asdf');

		$this->expectOutputString('asdf');
		$r->renderError($e);
	}

	public function testRenderErrorWithString(): void {
		$r = new Renderer($this->createStub(TemplateBase::class));

		$this->expectOutputString('asdf');
		$r->renderError('asdf');
	}
}
