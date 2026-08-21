<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\IgnoredNode;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Renderable;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;
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

	public function testWalkFilterSkipsSubtree(): void {
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
				$subtree = $this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->never()),
					$this->stubTreeNodeWithChildren(
						$this->mockLeafNodeExpectingGetValue($this->never()),
						$this->mockLeafNodeExpectingGetValue($this->never()),
					),
					$this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->never()),
				),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
		);

		Tree::walk($tree,
			fn (Node $n) => $n->isLeaf() ? $n->getValue() === null : true,
			fn (Node $n) => $n !== $subtree,
		);
	}

	public function testWalkFilterSkipsLeaves(): void {
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
					$n1 = $this->mockLeafNodeExpectingGetValue($this->never()),
					$n2 = $this->mockLeafNodeExpectingGetValue($this->never()),
					$this->mockLeafNodeExpectingGetValue($this->once()),
				),
				$this->mockLeafNodeExpectingGetValue($this->once()),
			),
			$this->mockLeafNodeExpectingGetValue($this->once()),
		);

		Tree::walk($tree,
			fn (Node $n) => $n->isLeaf() ? $n->getValue() === null : true,
			fn (Node $n) => $n !== $n1 && $n !== $n2,
		);
	}

	public function testMapStructureValuesMatchesStuctureData(): void {
		$tree = $this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $v1 = $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $v2 = $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $v3 = $this->createStub(Renderable::class)),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $v4 = $this->createStub(Renderable::class)),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $v5 = $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $v6 = $this->createStub(Renderable::class)),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->once(), $v7 = $this->createStub(Renderable::class)),
					$this->mockLeafNodeExpectingGetValue($this->once(), $v8 = $this->createStub(Renderable::class)),
					$this->mockLeafNodeExpectingGetValue($this->once(), $v9 = $this->createStub(Renderable::class)),
				),
				$this->mockLeafNodeExpectingGetValue($this->once(), $v10 = $this->createStub(Renderable::class)),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $v11 = $this->createStub(Renderable::class)),
		);

		$this->assertSame(
			[
				[
					$v1,
					$v2,
					$v3,
				],
				$v4,
				[
					$v5,
					$v6,
					[
						$v7,
						$v8,
						$v9,
					],
					$v10,
				],
				$v11,
			],
			Tree::map_structure_values($tree),
		);
	}

	public function testMapStructureTypesMatchesStuctureClasses(): void {
		$tree = $this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
				),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
		);

		$this->assertSame(
			[
				[Node::class => [
					[Node::class => 'Renderable'],
					[Node::class => 'Renderable'],
					[Node::class => 'Renderable'],
				]],
				[Node::class => 'Renderable'],
				[Node::class => [
					[Node::class => 'Renderable'],
					[Node::class => 'Renderable'],
					[Node::class => [
						[Node::class => 'Renderable'],
						[Node::class => 'Renderable'],
						[Node::class => 'Renderable'],
					]],
					[Node::class => 'Renderable'],
				]],
				[Node::class => 'Renderable'],
			],
			TestUtils\TreeUtils::map_tree_struct_parents(Tree::map_structure_types($tree)),
		);
	}

	public function testMapStructureTypesWithLeafStart(): void {
		$this->assertSame(
			[
				[Node::class => 'Renderable'],
			],
			TestUtils\TreeUtils::map_tree_struct_parents(
				[
					Tree::map_structure_types(
						$this->mockLeafNodeExpectingGetValue($this->once(), $this->createStub(Renderable::class)),
					),
				],
			),
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
		$tree = new TestUtils\WalkCountingTree($this->stubTreeNodeWithChildren(
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
		$tree = new TestUtils\WalkCountingTree($this->stubTreeNodeWithChildren(
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

		$tree = new TestUtils\WalkCountingTree($this->stubTreeNodeWithChildren(
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

		$tree = new TestUtils\WalkCountingTree($this->stubTreeNodeWithChildren(
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

	public function testAddNodeWithDefault(): void {
		$tree = new TestUtils\WalkCountingTree(Node::withChildren(
			$l1 = $this->mockLeafNode(),
		));
		$l2 = $tree->addNode();
		$this->assertInstanceOf(Node::class, $l2);
		$this->assertTrue($l2->isLeaf());
		$this->assertFalse($l2->hasValue());
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($l2));
		$this->assertSame(0, $tree::getCalls());
		$this->assertSame([$l1, $l2], [...$tree->root]);
	}

	public function testAddNodeWithLeafNode(): void {
		$tree = new TestUtils\WalkCountingTree(Node::withChildren(
			$l1 = $this->mockLeafNode(),
		));
		$newleaf = $this->mockLeafNode();
		$l2 = $tree->addNode($newleaf);
		$this->assertSame($newleaf, $l2);
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($l2));
		$this->assertSame(0, $tree::getCalls());
		$this->assertSame([$l1, $l2], [...$tree->root]);
	}

	public function testAddNodeWithTreeNode(): void {
		$tree = new TestUtils\WalkCountingTree(Node::withChildren(
			$l1 = $this->mockLeafNode(),
		));
		$newnode = $this->stubTreeNodeWithChildren(
			$c = $this->mockLeafNode(),
		);
		$l2 = $tree->addNode($newnode);
		$this->assertSame($newnode, $l2);
		$tree::resetCalls();
		$this->assertTrue($tree->containsNode($l2));
		$this->assertTrue($tree->containsNode($c));
		$this->assertSame(0, $tree::getCalls());
		$this->assertSame([$l1, $l2], [...$tree->root]);
	}

	public function testAddNodeWithNodeAlreadyInTree(): void {
		$tree = new TestUtils\WalkCountingTree(Node::withChildren(
			$l = $this->mockLeafNode(),
		));
		$this->expectException(ValueError::class);
		$tree->addNode($l);
	}

	public function testAddValueWithEmptyTree(): void {
		$val = $this->createStub(Renderable::class);
		$n = $this->mockLeafNode();
		$n
			->expects($this->once())
			->method('appendChildren')
			->willReturnCallback(function (Node ...$n) use ($val) {
				$this->assertContainsOnlyInstancesOf(Node::class, $n);
				$this->assertCount(1, $n);
				$this->assertTrue($n[0]->isLeaf());
				$this->assertSame($val, $n[0]->getValue());
			});
		$n
			->expects($this->never())
			->method('hasValue');
		$tree = new Tree($n);
		$tree->addValue($val);
	}

	public function testAddValueWithEmptyLeafNode(): void {
		$val = $this->createStub(Renderable::class);
		$n = $this->mockLeafNode();
		$n
			->expects($this->once())
			->method('hasValue')
			->willReturn(false);
		$n
			->expects($this->once())
			->method('setValue')
			->willReturnCallback(fn ($v) => $this->assertSame($val, $v));
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$n,
		));
		$tree->setCurrentNode($n);
		$tree->addValue($val);
	}

	public function testAddValueWithLeafNodeWithValue(): void {
		$val = $this->createStub(Renderable::class);
		$n = $this->mockLeafNode();
		$n
			->expects($this->once())
			->method('hasValue')
			->willReturn(true);
		$n
			->expects($this->once())
			->method('appendChildren')
			->willReturnCallback(function (Node ...$n) use ($val) {
				$this->assertContainsOnlyInstancesOf(Node::class, $n);
				$this->assertCount(1, $n);
				$this->assertTrue($n[0]->isLeaf());
				$this->assertSame($val, $n[0]->getValue());
			});
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$n,
		));
		$tree->setCurrentNode($n);
		$tree->addValue($val);
	}

	public function testRender(): void {
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf1\n")),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf2\n")),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf3\n")),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf4\n")),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf5\n")),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf6\n")),
				$this->stubTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf7\n")),
					$this->mockLeafNodeExpectingGetValue($this->once(), null),
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf8\n")),
					$this->mockLeafNodeExpectingGetValue($this->once(), null),
					$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf9\n")),
				),
				$this->mockLeafNodeExpectingGetValue($this->once(), null),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf10\n")),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf11\n")),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue(":")),
		));
		$this->expectOutputString(<<<"END"
		asdf1
		asdf2
		asdf3
		asdf4
		asdf5
		asdf6
		asdf7
		asdf8
		asdf9
		asdf10
		asdf11
		:
		END
		);
		$tree->render();
	}

	public function testRenderSkipsIgnoredNodes(): void {
		$tree = new Tree($this->stubTreeNodeWithChildren(
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf1\n")),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf2\n")),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf3\n")),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf4\n")),
			$this->stubTreeNodeWithChildren(
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf5\n")),
				$this->mockLeafNodeExpectingGetValue($this->never(), $this->stubRenderableValue("never"), nodeclass: IgnoredNode::class),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf6\n")),
				$this->stubIgnoredTreeNodeWithChildren(
					$this->mockLeafNodeExpectingGetValue($this->never(), $this->stubRenderableValue("never")),
					$this->mockLeafNodeExpectingGetValue($this->never(), $this->stubRenderableValue("never")),
				),
				$this->mockLeafNodeExpectingGetValue($this->once(), null),
				$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf10\n")),
			),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue("asdf11\n")),
			$this->mockLeafNodeExpectingGetValue($this->once(), $this->stubRenderableValue(":")),
		));
		$this->expectOutputString(<<<"END"
		asdf1
		asdf2
		asdf3
		asdf4
		asdf5
		asdf6
		asdf10
		asdf11
		:
		END
		);
		$tree->render();
	}
}
