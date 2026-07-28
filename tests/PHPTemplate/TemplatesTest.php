<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\StaticTemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\TextTemplate;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing()]
final class TemplatesTest extends TestCase {
	#[DataProvider('templatesProvider')]
	public function testTemplate(string $template, array $deps, string $expected): void {
		$r = new Renderer(
			new TextTemplate($template),
			new StaticTemplateResolver($deps),
		);
		$out = $r->renderToString();
		$this->assertEquals($expected, $out);
	}

	public static function templatesProvider(): array {
		return [
			'empty' => [
				<<<'INPUT'
				INPUT,
				[],
				'',
			],
			'basic text' => [
				<<<'INPUT'
				asdf
				INPUT,
				[],
				'asdf',
			],
			'basic code' => [
				<<<'INPUT'
				<?php
				echo "asdf";
				INPUT,
				[],
				'asdf',
			],
			'child template' => [
				<<<'INPUT'
				parent before
				<? self::tpl('child_tpl')() ?>
				parent after
				INPUT,
				[
					'child_tpl' => <<<'CHILD'
					child
					:
					CHILD,
				],
				<<<'EXPECTED'
				parent before
				child
				:parent after
				EXPECTED,
			],
		];
	}
}
