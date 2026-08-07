<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test;

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\StaticTemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function count;

#[CoversNothing()]
final class TemplatingTest extends TestCase {
	#[DataProvider('templatesProvider')]
	public function testTemplate(string $template, array $deps, string $expected): void {
		$r = new Renderer(
			new Templates\PHPString($template),
			new StaticTemplateResolver($deps),
		);
		$out = $r->renderToString();
		$this->assertEquals($expected, $out);
	}

	public static function templatesProvider(): iterable {
		foreach (require 'templating_testdata.php' as $name => $test) {
			if (count($test) < 3)
				array_splice($test, 1, 0, []);
			yield $name => $test;
		}
	}
}
