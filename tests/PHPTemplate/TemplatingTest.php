<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test;

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\StaticTemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

use function count;

#[CoversNothing()]

final class TemplatingTest extends TestCase {
	public const SUPPORTED_PHP_VERSIONS = [
		8.1,
		8.5,
	];

	#[DataProvider('templatesProviderVer8_1')]
	public function testTemplate(string $template, array $deps, string $expected): void	{
		$this->testTemplateImpl($template, $deps, $expected);
	}

	#[RequiresPhp('8.5')]
	#[DataProvider('templatesProviderVer8_5')]
	public function testPhp85Template(string $template, array $deps, string $expected): void	{
		$this->testTemplateImpl($template, $deps, $expected);
	}

	protected function testTemplateImpl(string $template, array $deps, string $expected): void {
		$r = Renderer::create(
			new Templates\PHPString($template),
			new StaticTemplateResolver($deps),
		);
		$out = $r->renderToString();
		$this->assertEquals($expected, $out);
	}

	public static function templatesProviderVer8_1(): iterable {
		return self::templatesProviderImpl(8.1);
	}
	public static function templatesProviderVer8_5(): iterable {
		return self::templatesProviderImpl(8.5);
	}

	protected static function templatesProviderImpl(float $php_ver): iterable {
		$php_ver_next = null;
		if (($k = array_search($php_ver, self::SUPPORTED_PHP_VERSIONS)) !== false)
			$php_ver_next = self::SUPPORTED_PHP_VERSIONS[$k + 1] ?? null;
		foreach (require 'templating_testdata.php' as $name => $test) {
			$test_min_ver = (string) ($test['php_min_ver'] ?? self::SUPPORTED_PHP_VERSIONS[0]);
			unset($test['php_min_ver']);
			if (
				version_compare($test_min_ver, (string) $php_ver, '<')
				|| ($php_ver_next != null && version_compare($test_min_ver, (string) $php_ver_next, '>='))
			) {
				continue;
			}
			if (count($test) < 3)
				array_splice($test, 1, 0, []);
			yield $name => $test;
		}
	}
}
