<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function is_string;

#[CoversClass(Renderer::class)]
final class RendererStatesTest extends TestCase {
	private function stubTemplate(Callable|string $content): Templates\Base {
		$cb = !is_string($content) ? $content : function (...$args) use ($content): void {
			echo $content;
		};
		$t = $this->createStub(Templates\Base::class);
		$t
			->method('execute')
			->willReturnCallback($cb);
		return $t;
	}

	public function testSequenceOfChildren(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create($this->createStub(Templates\Base::class),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('asdf'),
				$this->stubTemplate('qwer'),
				$this->stubTemplate('zxcv'),
			),
		);

		$r->renderChild($r->getTemplateAsProxy('test_tpl'));
		$r->renderChild($r->getTemplateAsProxy('test_tpl'));
		$r->renderChild($r->getTemplateAsProxy('test_tpl'));

		$this->assertJsonStringEqualsJsonString(
			json_encode(
				[
					"asdf",
					"qwer",
					"zxcv",
				]
			),
			json_encode(Tree::map_structure_values($r->rendertree->root)),
		);

		$this->expectOutputString('asdfqwerzxcv');
		$r->rendertree->render();
	}

	public function testNestedChild(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$this->stubTemplate('root'),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('asdf'),
				$this->stubTemplate(function (...$n) {
					echo "A";
					$n['renderer']->renderChild($n['renderer']->getTemplateAsProxy('test_tpl'));
					echo "B";
				}),
				$this->stubTemplate('qwer'),
				$this->stubTemplate('zxcv'),
			),
		);

		$r->renderChild($r->getTemplateAsProxy('test_tpl')); // asdf
		$r->renderChild($r->getTemplateAsProxy('test_tpl')); // A + qwer + B
		$r->renderChild($r->getTemplateAsProxy('test_tpl')); // zxcv

		$this->assertJsonStringEqualsJsonString(
			json_encode(
				[
					"asdf",
					[
						"A",
						[
							"qwer",
						],
						"B",
					],
					"zxcv",
				]
			),
			json_encode(Tree::map_structure_values($r->rendertree->root)),
		);

		$this->expectOutputString('asdfAqwerBzxcv');
		$r->rendertree->render();
	}
}
