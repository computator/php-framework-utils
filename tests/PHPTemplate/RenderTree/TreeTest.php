<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

use ArrayIterator;
use ValueError;

#[CoversClass(Tree::class)]
final class TreeTest extends TestCase {

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

	private function mockLeafNode() {
		$n = $this->createMock(Node::class);
		$n
			->method('isLeaf')
			->willReturn(true);
		return $n;
	}

	private function mockLeafNodeExpectingGetValue(InvocationOrder $order) {
		$n = $this->mockLeafNode();
		$n
			->expects($order)
			->method('getValue');
		return $n;
	}

	public function testWalkVisitsAllNodes(): void {
		$tree = $this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->once()),
					$this->mockLeafNodeExpectingGetValue($this->once()),
					$this->mockLeafNodeExpectingGetValue($this->once()),
				),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
		);

		Tree::walk($tree, fn (Node $n) => $n->isLeaf() ? $n->getValue() : true);
	}

	public function testWalkAbortInMiddleOnTreeNode(): void {
		$tree = $this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$abort_node = $this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->never()),
				),
				$this->mockLeafNodeExpectingGetValue($this->never()),
			),
			$this->mockLeafNodeExpectingGetValue($this->never()),
		);

		Tree::walk($tree, fn (Node $n) =>
			$n !== $abort_node
			&& (
				$n->isLeaf() ? $n->getValue() === null : true
			)
		);
	}

	public function testWalkAbortInMiddleOnLeafNode(): void {
		$tree = $this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->mockLeafNodeExpectingGetValue($this->once()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->once()),
					$abort_node = $this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->never()),
				),
				$this->mockLeafNodeExpectingGetValue($this->never()),
			),
			$this->mockLeafNodeExpectingGetValue($this->never()),
		);

		Tree::walk($tree, fn (Node $n) =>
			$n !== $abort_node
			&& (
				$n->isLeaf() ? $n->getValue() === null : true
			)
		);
	}

	public function testWalkWithSingleLeafNode(): void {
		Tree::walk(
			$this->mockLeafNodeExpectingGetValue($this->once()),
			fn (Node $n) => $n->isLeaf() ? $n->getValue() : true,
		);
	}

	public function testIsEmptyWithLeaf(): void {
		$n = $this->createMock(Node::class);
		$n
			->expects($this->atLeastOnce())
			->method('isLeaf')
			->willReturn(true);
		$t = new Tree($n);
		$this->assertTrue($t->isEmpty());
	}

	public function testIsEmptyWithNonLeaf(): void {
		$n = $this->createMock(Node::class);
		$n
			->expects($this->atLeastOnce())
			->method('isLeaf')
			->willReturn(false);
		$t = new Tree($n);
		$this->assertfalse($t->isEmpty());
	}

	public function testContainsNodeWithExistingInOriginalTreeSkipsWalk(): void {
		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$this->mockLeafNode(),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$tgt = $this->mockLeafNode(),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$this->mockLeafNode(),
					$this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($tgt));
		$this->assertSame(0, $tree::getCalls());
	}

	public function testContainsNodeWithMissingInOriginalTreeWalks(): void {
		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$this->mockLeafNode(),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$this->mockLeafNode(),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$this->mockLeafNode(),
					$this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$tree::resetCalls();
		$this->assertFalse($tree->containsNode($this->mockLeafNode()));
		$this->assertSame(1, $tree::getCalls());
	}

	public function testContainsNodeWithExistingInMutatedTreeWalks(): void {
		$mutating = $this->createStub(Node::class);
		$mutating
			->method('isLeaf')
			->willReturnOnConsecutiveCalls(true, false);
		$mutating
			->method('getIterator')
			->willReturn(new ArrayIterator([
				$this->mockLeafNode(),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$tgt = $this->mockLeafNode(),
					$this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			]));

		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$this->mockLeafNode(),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$mutating,
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$this->mockLeafNode(),
					$this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($tgt));
		$this->assertSame(1, $tree::getCalls());
	}

	public function testContainsNodeWithExistingInMutatedTreeRepeatSkipsWalk(): void {
		$mutating = $this->createStub(Node::class);
		$mutating
			->method('isLeaf')
			->willReturnOnConsecutiveCalls(true, false);
		$mutating
			->method('getIterator')
			->willReturn(new ArrayIterator([
				$this->mockLeafNode(),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$tgt2 = $this->mockLeafNode(),
					$tgt1 = $this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			]));

		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$this->mockLeafNode(),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
				$mutating,
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNode(),
					$this->mockLeafNode(),
					$this->mockLeafNode(),
				),
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$this->assertTrue($tree->containsNode($tgt1));
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($tgt2));
		$this->assertSame(0, $tree::getCalls());
	}

	public function testGetCurrentNode(): void {
		$tree = new Tree(
			$tgt = $this->mockLeafNode(),
		);
		$this->assertSame($tgt, $tree->getCurrentNode());
	}

	public function testSetCurrentNodeWithNodeInTree(): void {
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$tgt = $this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$this->assertNotSame($tgt, $tree->getCurrentNode());
		$tree->setCurrentNode($tgt);
		$this->assertSame($tgt, $tree->getCurrentNode());
	}

	public function testSetCurrentNodeWithNodeOutsideTree(): void {
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$this->mockLeafNode(),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNode(),
			),
			$this->mockLeafNode(),
		));
		$this->expectException(ValueError::class);
		$tree->setCurrentNode($this->mockLeafNode());
	}
}
