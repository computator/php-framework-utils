<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Renderable;
use Iterator;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use ArgumentCountError;
use function PHPUnit\Framework\assertNull;

#[CoversClass(Node::class)]
final class NodeTest extends TestCase {
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
		$this->assertSame([$c1, $c2], [...$n]);
	}

	public function testWithChildrenRequiresValues(): void {
		$this->expectException(ArgumentCountError::class);
		Node::withChildren();
	}

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

	public function testGetValueWithNullValue(): void {
		$this->assertNull(Node::withValue(null)->getValue());
	}

	public function testGetValueWithRenderable(): void {
		$r = $this->createStub(Renderable::class);
		$this->assertSame($r, Node::withValue($r)->getValue());
	}

	public function testGetValueWithChildren(): void {
		$this->expectException(LogicException::class);
		Node::withChildren($this->createStub(Node::class))->getValue();
	}

	public function testHasValueWithNullValue(): void {
		$this->assertFalse(Node::withValue(null)->hasValue());
	}

	public function testHasValueWithRenderable(): void {
		$this->assertTrue(Node::withValue($this->createStub(Renderable::class))->hasValue());
	}

	public function testHasValueWithChildren(): void {
		$this->expectException(LogicException::class);
		Node::withChildren($this->createStub(Node::class))->hasValue();
	}

	public function testSetValueWithPreviousNullValue(): void {
		$r = $this->createStub(Renderable::class);
		$n = Node::withValue(null);
		$this->assertNull($n->setValue($r));
		$this->assertSame($r, $n->getValue());
	}

	public function testSetValueWithPreviousRenderable(): void {
		$r1 = $this->createStub(Renderable::class);
		$r2 = $this->createStub(Renderable::class);
		$n = Node::withValue($r1);
		$this->assertSame($r1, $n->setValue($r2));
		$this->assertSame($r2, $n->getValue());
	}

	public function testSetValueWithChildren(): void {
		$this->expectException(LogicException::class);
		Node::withChildren($this->createStub(Node::class))->setValue($this->createStub(Renderable::class));
	}

	public function testAppendChildrenWithLeafContainingNullValue(): void {
		$c = $this->createStub(Node::class);
		$n = Node::withValue(null);
		$n->appendChildren($c);
		$this->assertFalse($n->isLeaf());
		$this->assertSame([$c], [...$n]);
	}

	public function testAppendChildrenWithLeafContainingRenderableValue(): void {
		$r = $this->createStub(Renderable::class);
		$c = $this->createStub(Node::class);
		$n = Node::withValue($r);
		$n->appendChildren($c);
		$this->assertFalse($n->isLeaf());
		$out = [...$n];
		$this->assertContainsOnlyInstancesOf(Node::class, $out);
		$this->assertCount(2, $out);
		$this->assertSame($r, $out[0]->getValue());
		$this->assertSame($c, $out[1]);
	}

	public function testAppendChildrenWithTreeNode(): void {
		$c1 = $this->createStub(Node::class);
		$c2 = $this->createStub(Node::class);
		$n = Node::withChildren($c1);
		$n->appendChildren($c2);
		$this->assertSame([$c1, $c2], [...$n]);
	}

	public function testAppendChildrenWithMultipleNodes(): void {
		$n = Node::withChildren($this->createStub(Node::class));
		$n->appendChildren(
			$this->createStub(Node::class),
			$this->createStub(Node::class),
			$this->createStub(Node::class),
		);
		$this->assertCount(4, $n);
	}

	public function testIteratorAggregate(): void {
		$n = Node::withValue(null);
		$n->appendChildren(
			$this->createStub(Node::class),
			$this->createStub(Node::class),
			$this->createStub(Node::class),
		);
		$i = $n->getIterator();
		$this->assertInstanceOf(Iterator::class, $i);
		$this->assertContainsOnlyInstancesOf(Node::class, $i);
	}
}
