<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\RenderManager;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Buffer;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\IgnoredNode;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function is_string;

#[CoversNothing]
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

	public function testRenderChildSequenceOfChildren(): void {
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

	public function testRenderChildNestedChild(): void {
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

	public function testStartRenderingBlockStructure(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$t = $this->stubTemplate(''),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('parent'),
			),
		);

		$r->setParentForTemplate($t, 'parent_tpl');
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'testblock');
		ob_end_clean();

		$this->assertSame(
			[
				[Node::class => Buffer::class],
				[IgnoredNode::class => Buffer::class],
			],
			Tree::map_structure_types($r->rendertree->root),
		);

		$this->assertJsonStringEqualsJsonString(
			json_encode(
				[
					'',
					'',
				]
			),
			json_encode(Tree::map_structure_values($r->rendertree->root)),
		);

		$this->assertSame($r->rendertree->root->getIterator()[1], $r->rendertree->getCurrentNode());
	}

	public function testEndRenderingBlockStructure(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$t = $this->stubTemplate(''),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('parent'),
			),
		);

		$r->setParentForTemplate($t, 'parent_tpl');
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'testblock');
		$r->endRenderingBlock($t);
		ob_end_clean();

		$this->assertSame(
			[
				[Node::class => Buffer::class],
				[IgnoredNode::class => Buffer::class],
				[Node::class => Buffer::class],
			],
			Tree::map_structure_types($r->rendertree->root),
		);

		$this->assertJsonStringEqualsJsonString(
			json_encode(
				[
					'',
					'',
					'',
				]
			),
			json_encode(Tree::map_structure_values($r->rendertree->root)),
		);

		$this->assertSame($r->rendertree->root, $r->rendertree->getCurrentNode());
	}

	public function testRenderingBlocksUseIgnoredNodeTree(): void {
		/** @var RenderManager|TestUtils\VisibleRenderer $r */
		$r = TestUtils\VisibleRenderer::create(
			$t = $this->stubTemplate(''),
			new TestUtils\QueueTemplateResolver(
				$this->stubTemplate('parent'),
				$this->stubTemplate('child'),
			),
		);

		$r->setParentForTemplate($t, 'parent_tpl');
		$r->swap_to_new_buffer();
		$r->startRenderingBlock($t, 'testblock');
		$r->renderChild($r->getTemplateAsProxy('child_tpl'));
		$r->endRenderingBlock($t);
		$r->complete_buffer();

		$this->assertSame(
			[
				[Node::class => Buffer::class],
				[IgnoredNode::class => [
					[Node::class => Buffer::class],
					[Node::class => [
						[Node::class => Buffer::class],
					]],
					[Node::class => Buffer::class],
				]],
				[Node::class => Buffer::class],
			],
			Tree::map_structure_types($r->rendertree->root),
		);

		$this->assertJsonStringEqualsJsonString(
			json_encode(
				[
					'',
					[
						'',
						[
							'child',
						],
						'',
					],
					'',
				]
			),
			json_encode(Tree::map_structure_values($r->rendertree->root)),
		);

		$this->expectOutputString('');
		$r->rendertree->render();
	}
}
