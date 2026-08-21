<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\Templates;

use function is_string;

trait StubTemplate {
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
}
