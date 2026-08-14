<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

use Exception;
use ReflectionProperty;

use function ob_start;

#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase {
	private static function getRendertree(Renderer $renderer): RenderTree\Tree {
		static $prop = new ReflectionProperty(Renderer::class, 'rendertree');
		return $prop->getValue($renderer);
	}

	public function testRender(): void {
		$t = $this->createStub(Templates\Base::class);
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		$r = Renderer::create($t);
		$this->expectOutputString('asdf');
		$r->render();
	}

	public function testRenderToString(): void {
		$t = $this->createStub(Templates\Base::class);
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		$r = Renderer::create($t);
		$this->expectOutputString('');
		$rv = $r->renderToString();
		$this->assertSame('asdf', $rv);
	}

	public function testRenderTemplateWithError(): void {
		$t = $this->createStub(Templates\Base::class);
		$t
			->method('execute')
			->willThrowException(new Exception('an error'));
		$r = Renderer::create($t);
		$this->expectOutputString('');
		$this->expectException(Exceptions\TemplateRenderException::class);
		$r->render();
	}

	public function testRenderTemplateWithMismatchedOutputBuffering(): void {
		$t = $this->createStub(Templates\Base::class);
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
				ob_start();
				echo 'qwer';
			});
		$r = Renderer::create($t);
		$this->expectOutputString('asdfqwer');
		$this->expectException(Exceptions\TemplateRenderException::class);
		$this->expectExceptionMessageMatches('/output buffer/');
		$r->render();
	}

	public function testTemplateExecuteContext(): void {
		$r = null;
		$t = $this->createMock(Templates\Base::class);
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
		$r = Renderer::create($t);
		$r->renderToString();
	}

	public function testGetTemplateAsProxyTemplateMatches(): void {
		$res = new class ([
			$t = $this->createStub(Templates\Base::class),
			$this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);
		$p = $r->getTemplateAsProxy('test_tpl');

		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p));
	}

	public function testGetTemplateAsProxyReturnsUnique(): void {
		$res = new class ([
			$this->createStub(Templates\Base::class),
			$this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);
		$p1 = $r->getTemplateAsProxy('test_tpl');
		$p2 = $r->getTemplateAsProxy('test_tpl');
		$this->assertNotSame($p1, $p2);
		$this->assertNotSame($p1->id, $p2->id);
		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertNotSame($tpl_prop->getValue($p1), $tpl_prop->getValue($p2));
	}

	public function testGetTemplateInstanceAsProxyByIdWithExistingId(): void {
		$res = new class ([
			$t = $this->createStub(Templates\Base::class),
			$this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);
		$p1 = $r->getTemplateAsProxy('test_tpl');
		$p2 = $r->getTemplateInstanceAsProxyById($p1->id);
		$this->assertNotSame($p1, $p2);
		$this->assertNotSame($p1->id, $p2->id);
		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p1));
		$this->assertSame($tpl_prop->getValue($p1), $tpl_prop->getValue($p2));
	}

	public function testGetTemplateInstanceAsProxyByIdWithUnknownId(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class));
		$p = $r->getTemplateInstanceAsProxyById(1234);
		$this->assertNull($p);
	}

	public function testRenderChild(): void {
		$res = new class ([
			$t = $this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		$t
			->method('execute')
			->willReturnCallback(function (...$args): void {
				echo 'asdf';
			});
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);

		$p = $r->getTemplateAsProxy('test_tpl');
		$this->assertSame($t, (new ReflectionProperty(TemplateRenderProxy::class, 'tpl'))->getValue($p));

		$r->renderChild($p);
		$this->expectOutputString('asdf');
		self::getRendertree($r)->render();
	}

	public function testRenderErrorWithStringTemplate(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class));
		$e = new Templates\Text('asdf');

		$r->renderError($e);
		$this->expectOutputString('asdf');
		self::getRendertree($r)->render();
	}

	public function testRenderErrorWithString(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class));

		$r->renderError('asdf');
		$this->expectOutputString('asdf');
		self::getRendertree($r)->render();
	}

	#[DoesNotPerformAssertions]
	public function testSetParentForTemplate(): void {
		$res = new class ([
			$this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);
		$r->setParentForTemplate($this->createStub(Templates\Base::class), 'asdf');
	}

	public function testSetParentForTemplateTwiceThrows(): void {
		$res = new class ([
			$this->createStub(Templates\Base::class),
		]) extends TemplateResolver {
			public function __construct(
				protected $tpls,
			) {}
			protected function map(string $template): Templates\Base {
				return array_shift($this->tpls);
			}
		};
		$t = $this->createStub(Templates\Base::class);
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class), $res);
		$r->setParentForTemplate($t, 'asdf');
		$this->expectException(Exceptions\RendererException::class);
		$r->setParentForTemplate($t, 'asdf');
	}
}
