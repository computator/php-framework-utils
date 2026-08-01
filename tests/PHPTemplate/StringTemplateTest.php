<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\StringTemplate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringTemplate::class)]
final class StringTemplateTest extends TestCase {
	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$t = new StringTemplate($tpl);
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
		$t = new StringTemplate("asdf\nqwer");
		$this->expectOutputString("asdf\nqwer");
		$rv = $t->execute([]);
		$this->assertNull($rv);
	}

	public function testExecuteDoesNotRunPhp(): void {
		$t = new StringTemplate('<?php echo "hello"; ?>');
		$this->expectOutputString('<?php echo "hello"; ?>');
		$t->execute([]);
	}
}
