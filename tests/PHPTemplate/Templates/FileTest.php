<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\Templates;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Templates\File::class)]
final class FileTest extends TestCase {
	public function testEmptyPath(): void {
		$this->expectException(Exceptions\TemplateNotFoundException::class);
		new Templates\File('');
	}

	public function testNonexistentFile(): void {
		$this->expectException(Exceptions\TemplateNotFoundException::class);
		new Templates\File('nonexistent_file_asdf');
	}

	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		fwrite($fd, $tpl);

		$t = new Templates\File($path);
		$this->assertEquals($exp, $t->get_contents(...$args));

		fclose($fd);
	}

	public static function getContentsProvider(): array {
		return [
			'defaults' => ['asdf', [], 'asdf'],
			'offset' => ['asdf', ['offset' => 2], 'df'],
			'length' => ['asdf', ['length' => 2], 'as'],
			'offset and length' => ['asdf', ['offset' => 1, 'length' => 2], 'sd'],
		];
	}

	public function testGetContentsError(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		$t = new Templates\File($path);
		fclose($fd);

		$this->expectException(Exceptions\TemplateRenderException::class);
		$t->get_contents();
	}

	public function testExecuteOutput(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		fwrite($fd, <<<'TPL'
			before
			<?php
			echo "$var";
			return 42;
			?>
			after
			TPL
		);

		$t = new Templates\File($path);
		$this->expectOutputString("before\nasdf");
		$rv = $t->execute(
			['var' => 'asdf'],
			...TemplateRuntimeController::getConstructorTestArgs($this),
		);
		$this->assertEquals(42, $rv);

		fclose($fd);
	}

	public function testVerifyUsesRootNamespace(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		fwrite($fd, <<<'TPL'
			<?php
			return __NAMESPACE__;
			TPL
		);

		$t = new Templates\File($path);
		$rv = $t->execute(
			[],
			...TemplateRuntimeController::getConstructorTestArgs($this),
		);
		$this->assertEquals('', $rv);

		fclose($fd);
	}
}
