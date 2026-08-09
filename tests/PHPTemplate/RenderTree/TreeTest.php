<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

use ArrayIterator;

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

	private function mockLeafNodeExpectingGetValue(InvocationOrder $order) {
		$n = $this->createMock(Node::class);
		$n
			->method('isLeaf')
			->willReturn(true);
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
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$tgt = $this->mockLeafNodeExpectingGetValue($this->any()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
		));
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($tgt));
		$this->assertSame(0, $tree::getCalls());
	}

	public function testContainsNodeWithMissingInOriginalTreeWalks(): void {
		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
		));
		$tree::resetCalls();
		$this->assertFalse($tree->containsNode($this->mockLeafNodeExpectingGetValue($this->any())));
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
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$tgt = $this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			]));

		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$mutating,
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
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
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$tgt2 = $this->mockLeafNodeExpectingGetValue($this->any()),
					$tgt1 = $this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			]));

		$tree = new WalkCountingTree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->any()),
				$mutating,
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
					$this->mockLeafNodeExpectingGetValue($this->any()),
				),
				$this->mockLeafNodeExpectingGetValue($this->any()),
			),
			$this->mockLeafNodeExpectingGetValue($this->any()),
		));
		$this->assertTrue($tree->containsNode($tgt1));
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($tgt2));
		$this->assertSame(0, $tree::getCalls());
	}
}
