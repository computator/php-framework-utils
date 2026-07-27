<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;
use Computator\FrameworkUtils\PHPTemplate\TextTemplate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TextTemplateTest extends TestCase {
	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$t = new TextTemplate($tpl);
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

	public function testExecuteOutput(): void {
		$t = new TextTemplate(<<<'TPL'
			before
			<?php
			echo "$var";
			return 42;
			?>
			after
			TPL
		);
		$this->expectOutputString("before\nasdf");
		$rv = $t->execute(
			['var' => 'asdf'],
			...TemplateRuntimeController::getConstructorTestArgs($this),
		);
		$this->assertEquals(42, $rv);
	}

	public function testVerifyUsesRootNamespace(): void {
		$t = new TextTemplate(<<<'TPL'
			<?php
			return __NAMESPACE__;
			TPL
		);
		$rv = $t->execute(
			[],
			...TemplateRuntimeController::getConstructorTestArgs($this),
		);
		$this->assertEquals('', $rv);
	}
}
