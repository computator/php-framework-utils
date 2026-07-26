<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\FileTemplate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileTemplateTest extends TestCase {
	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		fwrite($fd, $tpl);

		$t = new FileTemplate($path);
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

		$t = new FileTemplate($path);
		$this->expectOutputString("before\nasdf");
		$rv = $t->execute(var: 'asdf');
		$this->assertEquals(42, $rv);

		fclose($fd);
	}
}
