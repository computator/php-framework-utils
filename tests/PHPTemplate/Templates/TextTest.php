<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\Templates;

use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Templates\Text::class)]
final class TextTest extends TestCase {
	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$t = new Templates\Text($tpl);
		$this->assertEquals($exp, $t->get_contents(...$args));
	}

	public static function getContentsProvider(): array {
		return [
			'defaults' => ['asdf', [], 'asdf'],
			'offset' => ['asdf', ['offset' => 2], 'df'],
			'length' => ['asdf', ['length' => 2], 'as'],
			'offset and length' => ['asdf', ['offset' => 1, 'length' => 2], 'sd'],
		];
	}

	public function testExecutePrintsContent(): void {
		$t = new Templates\Text("asdf\nqwer");
		$this->expectOutputString("asdf\nqwer");
		$rv = $t->execute([]);
		$this->assertNull($rv);
	}

	public function testExecuteDoesNotRunPhp(): void {
		$t = new Templates\Text('<?php echo "hello"; ?>');
		$this->expectOutputString('<?php echo "hello"; ?>');
		$t->execute([]);
	}
}
