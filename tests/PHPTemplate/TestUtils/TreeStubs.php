<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\IgnoredNode;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Renderable;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

use ArrayIterator;
use function is_string;

trait TreeStubs {
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

	private function stubTreeNodeWithChildren(Node ...$children) {
		$n = $this->createStub(Node::class);
		$n
			->method('isLeaf')
			->willReturn(false);
		$n
			->method('getIterator')
			->willReturn(new ArrayIterator($children));
		return $n;
	}

	private function stubIgnoredTreeNodeWithChildren(Node ...$children) {
		$n = $this->createStub(IgnoredNode::class);
		$n
			->method('isLeaf')
			->willReturn(false);
		$n
			->method('getIterator')
			->willReturn(new ArrayIterator($children));
		return $n;
	}

	private function mockLeafNode(string $nodeclass = Node::class) {
		$n = $this->createMock($nodeclass);
		$n
			->method('isLeaf')
			->willReturn(true);
		return $n;
	}

	private function mockLeafNodeExpectingGetValue(InvocationOrder $order, ?Renderable $value = null, string $nodeclass = Node::class) {
		$n = $this->mockLeafNode($nodeclass);
		$n
			->expects($order)
			->method('getValue')
			->willReturn($value);
		return $n;
	}

	private function stubRenderableValue(String $value) {
		$n = $this->createStub(Renderable::class);
		$n
			->method('render')
			->willReturnCallback(fn (): bool => (bool) print $value);
		return $n;
	}
}
