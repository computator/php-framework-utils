<?php

declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\RenderTree;

use Computator\FrameworkUtils\PHPTemplate\RenderTree\Buffer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass(Buffer::class)]
final class BufferTest extends TestCase {
	public function testGetContents(): void {
		$b = new Buffer('asdf');
		$this->assertSame('asdf', $b->getContents());
	}

	public function testAppend(): void {
		$b = new Buffer('asdf');
		$b->append('qwer');
		$this->assertSame('asdfqwer', $b->getContents());
	}

	public function testRender(): void {
		$b = new Buffer('asdf');
		$this->expectOutputString('asdf');
		$b->render();
	}

	public function testStringable(): void {
		$b = new Buffer('asdf');
		$this->assertSame('asdf', (string) $b);
	}

	public function testJsonSerializable(): void {
		$b = new Buffer('asdf');
		$this->assertJsonStringEqualsJsonString('"asdf"', json_encode($b));
	}
}
