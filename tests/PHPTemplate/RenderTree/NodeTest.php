<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Renderable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use ArgumentCountError;

#[CoversClass(Node::class)]
final class NodeTest extends TestCase {
	public function testIsLeafWithNullValue(): void {
		$n = Node::withValue(null);
		$this->assertTrue($n->isLeaf());
	}

	public function testIsLeafWithRenderableValue(): void {
		$n = Node::withValue($this->createStub(Renderable::class));
		$this->assertTrue($n->isLeaf());
	}

	public function testIsLeafWithChildren(): void {
		$n = Node::withChildren($this->createStub(Node::class));
		$this->assertFalse($n->isLeaf());
	}

	public function testWithValueCreatesLeafNodeWithValue(): void {
		$v = $this->createStub(Renderable::class);
		$n = Node::withValue($v);
		$this->assertTrue($n->isLeaf());
		$this->assertSame($v, $n->getValue());
	}

	public function testWithChildrenCreatesTreeNodeWithChildren(): void {
		$c1 = $this->createStub(Node::class);
		$c2 = $this->createStub(Node::class);
		$n = Node::withChildren($c1, $c2);
		$this->assertFalse($n->isLeaf());
		$this->assertNull($n->getValue());
		$this->assertSame([$c1, $c2], [...$n]);
	}

	public function testWithChildrenRequiresValues(): void {
		$this->expectException(ArgumentCountError::class);
		Node::withChildren();
	}
}
