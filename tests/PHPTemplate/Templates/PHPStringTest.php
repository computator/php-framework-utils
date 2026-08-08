<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\Templates;

use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Templates\PHPString::class)]
final class PHPStringTest extends TestCase {
	#[DataProvider('getContentsProvider')]
	public function testGetContents(string $tpl, array $args, string $exp): void {
		$t = new Templates\PHPString($tpl);
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
		$t = new Templates\PHPString(<<<'TPL'
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
		$t = new Templates\PHPString(<<<'TPL'
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
