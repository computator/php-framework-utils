<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Node;
use Computator\FrameworkUtils\PHPTemplate\RenderTree\Tree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tree::class)]
final class TreeTest extends TestCase {
	public function testIsEmptyWithLeaf(): void {
		$n = $this->createMock(Node::class);
		$n
			->expects($this->once())
			->method('isLeaf')
			->willReturn(true);
		$t = new Tree($n);
		$this->assertTrue($t->isEmpty());
	}

	public function testIsEmptyWithNonLeaf(): void {
		$n = $this->createMock(Node::class);
		$n
			->expects($this->once())
			->method('isLeaf')
			->willReturn(false);
		$t = new Tree($n);
		$this->assertfalse($t->isEmpty());
	}
}
