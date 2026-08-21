<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderObjects\TemplateRenderProxy;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;
use Computator\FrameworkUtils\Test\PHPTemplate\TestUtils\VisibleRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

use Exception;
use ReflectionProperty;

use ValueError;
use function ob_start;

#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase {

	use TestUtils\TreeStubs;

	public function testRender(): void {
		$r = Renderer::create(
			$this->stubTemplate('asdf'),
		);
		$this->expectOutputString('asdf');
		$r->render();
	}

	public function testRenderWithInherit(): void {
		$r = Renderer::create(
			$this->stubTemplate(function (...$args) {
				['renderer' => $tr, 'template' => $tt] = $args;
				$tr->setParentForTemplate($tt, 'test_parent');
				$tr->startRenderingBlock($tt, 'block');
				echo 'qwer';
				$tr->endRenderingBlock($tt);
			}),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('asdf'),
			),
		);
		$this->expectOutputString('asdf');
		$r->render();
	}

	public function testRenderToString(): void {
		$r = Renderer::create(
			$this->stubTemplate('asdf'),
		);
		$this->expectOutputString('');
		$rv = $r->renderToString();
		$this->assertSame('asdf', $rv);
	}

	public function testRenderToStringWithInherit(): void {
		$r = Renderer::create(
			$this->stubTemplate(function (...$args) {
				['renderer' => $tr, 'template' => $tt] = $args;
				$tr->setParentForTemplate($tt, 'test_parent');
				$tr->startRenderingBlock($tt, 'block');
				echo 'qwer';
				$tr->endRenderingBlock($tt);
			}),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('asdf'),
			),
		);
		$this->expectOutputString('');
		$rv = $r->renderToString();
		$this->assertSame('asdf', $rv);
	}

	public function testRenderWithMultiInherit(): void {
		$r = Renderer::create(
			$this->stubTemplate(function (...$args) {
				['renderer' => $tr, 'template' => $tt] = $args;
				$tr->setParentForTemplate($tt, 'test_parent');
				$tr->startRenderingBlock($tt, 'block');
				echo 'qwer';
				$tr->endRenderingBlock($tt);
			}),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate(function (...$args) {
					['renderer' => $tr, 'template' => $tt] = $args;
					$tr->setParentForTemplate($tt, 'test_parent2');
					$tr->startRenderingBlock($tt, 'block');
					echo 'zxcv';
					$tr->endRenderingBlock($tt);
				}),
				$this->stubTemplate('asdf'),
			),
		);
		$this->expectOutputString('asdf');
		$r->render();
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
		$t = $this->stubTemplate(function (...$args): void {
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
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$t = $this->createStub(Templates\Base::class),
				$this->createStub(Templates\Base::class),
			),
		);
		$p = $r->getTemplateAsProxy('test_tpl');

		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertSame($t, $tpl_prop->getValue($p));
	}

	public function testGetTemplateAsProxyReturnsUnique(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$this->createStub(Templates\Base::class),
				$this->createStub(Templates\Base::class),
			),
		);
		$p1 = $r->getTemplateAsProxy('test_tpl');
		$p2 = $r->getTemplateAsProxy('test_tpl');
		$this->assertNotSame($p1, $p2);
		$this->assertNotSame($p1->id, $p2->id);
		$tpl_prop = new ReflectionProperty(TemplateRenderProxy::class, 'tpl');
		$this->assertNotSame($tpl_prop->getValue($p1), $tpl_prop->getValue($p2));
	}

	public function testGetTemplateInstanceAsProxyByIdWithExistingId(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$t = $this->createStub(Templates\Base::class),
				$this->createStub(Templates\Base::class),
			),
		);
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

	public function testRenderChildIsolated(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$t = $this->stubTemplate('asdf'),
			),
		);

		$p = $r->getTemplateAsProxy('test_tpl');
		$this->assertSame($t, (new ReflectionProperty(TemplateRenderProxy::class, 'tpl'))->getValue($p));

		$r->renderChild($p);
		$this->expectOutputString('asdf');
		$r->rendertree->render();
	}

	public function testRenderChildWhileRendering(): void {
		$p = null;
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$tp = $this->stubTemplate(function (...$args) use (&$p): void {
				echo 'asdf';
				$args['renderer']->renderChild($p);
				echo 'qwer';
			}),
			new TestUtils\QueueTemplateResolver(
				$tc = $this->stubTemplate('zxcv'),
			),
		);

		$p = $r->getTemplateAsProxy('test_tpl');
		$this->assertSame($tc, (new ReflectionProperty(TemplateRenderProxy::class, 'tpl'))->getValue($p));

		$this->expectOutputString('asdfzxcvqwer');
		$r->render();
	}

	public function testRenderChildWithInherit(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate(function (...$args) {
					['renderer' => $tr, 'template' => $tt] = $args;
					$tr->setParentForTemplate($tt, 'test_parent');
					$tr->startRenderingBlock($tt, 'block');
					echo 'qwer';
					$tr->endRenderingBlock($tt);
				}),
				$this->stubTemplate('asdf'),
			),
		);

		$proxy = $r->getTemplateAsProxy('test_tpl');

		$r->renderChild($proxy);
		$this->expectOutputString('asdf');
		$r->rendertree->render();
	}

	public function testRenderErrorWithStringTemplate(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create($this->createStub(Templates\Base::class));
		$e = new Templates\Text('asdf');

		$r->renderError($e);
		$this->expectOutputString('asdf');
		$r->rendertree->render();
	}

	public function testRenderErrorWithString(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create($this->createStub(Templates\Base::class));

		$r->renderError('asdf');
		$this->expectOutputString('asdf');
		$r->rendertree->render();
	}

	#[DoesNotPerformAssertions]
	public function testSetParentForTemplate(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$this->createStub(Templates\Base::class),
			),
		);
		$r->setParentForTemplate($this->createStub(Templates\Base::class), 'asdf');
	}

	public function testSetParentForTemplateTwiceThrows(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$this->createStub(Templates\Base::class),
			),
		);
		$t = $this->createStub(Templates\Base::class);
		$r->setParentForTemplate($t, 'asdf');
		$this->expectException(Exceptions\RendererException::class);
		$r->setParentForTemplate($t, 'asdf');
	}

	public function testStartRenderingBlockWithEmptyNameThrows(): void {
		/** @var RenderManager $r */
		$r = Renderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$this->expectException(ValueError::class);
		$r->startRenderingBlock($t, '');
	}

	public function testStartRenderingBlockWithNoParentThrows(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$this->expectException(Exceptions\RendererStateException::class);
		$r->startRenderingBlock($t, 'asdf');
	}

	public function testStartRenderingBlockWithOpenBlockThrows(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$r->tpl_state($t)->parent = $this->createStub(Templates\Base::class);
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'asdf');
		ob_end_clean();
		$this->expectException(Exceptions\RendererStateException::class);
		$r->startRenderingBlock($t, 'qwer');
	}

	public function testStartRenderingBlockWithDuplicateNameThrows(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$r->tpl_state($t)->parent = $this->createStub(Templates\Base::class);
		$r->tpl_state($t)->blocks['asdf'] = $this->createStub(RenderTree\Node::class);
		$this->expectException(Exceptions\RendererStateException::class);
		$r->startRenderingBlock($t, 'asdf');
	}

	public function testStartRenderingBlockSuccess(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$r->tpl_state($t)->parent = $this->createStub(Templates\Base::class);
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'asdf');
		ob_end_clean();
		$this->assertSame('asdf', $r->tpl_state($t)->current_block);
		$this->assertArrayHasKey('asdf', $r->tpl_state($t)->blocks);
		$this->assertSame($r->tpl_state($t)->blocks['asdf'], $r->rendertree->getCurrentNode());
	}

	public function testEndRenderingBlockWithoutOpenBlockThrows(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$this->expectException(Exceptions\RendererStateException::class);
		$r->endRenderingBlock($t);
	}

	public function testEndRenderingBlockSuccess(): void {
		/** @var RenderManager|VisibleRenderer $r */
		$r = VisibleRenderer::create($this->createStub(Templates\Base::class));
		$t = $this->createStub(Templates\Base::class);
		$r->tpl_state($t)->parent = $this->createStub(Templates\Base::class);
		$before = $r->rendertree->getCurrentNode();
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'asdf');
		$r->endRenderingBlock($t);
		ob_end_clean();
		$this->assertNull($r->tpl_state($t)->current_block);
		$this->assertArrayHasKey('asdf', $r->tpl_state($t)->blocks);
		$this->assertNotSame($before, $r->tpl_state($t)->blocks['asdf']);
		$this->assertSame($before, $r->rendertree->getCurrentNode());
	}

}
