<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate;

use stdClass;
use TypeError;
use ValueError;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateResolver::class)]
final class TemplateResolverTest extends TestCase {
	public function testNewClassMustBeTemplateClass(): void {
		$this->expectException(TypeError::class);
		new TemplateResolver(stdClass::class);
	}

	public function testTemplateFoundReturnsTemplate(): void {
		$tc_success = new class extends Templates\Base {
			public function __construct() {}
			public function execute(array $context, mixed ...$controller_args): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};

		$resolved = (new TemplateResolver($tc_success::class))->resolve('asdf');
		$this->assertInstanceOf($tc_success::class, $resolved);
	}

	public function testTemplateNotFoundThrows(): void {
		$tc_fail = new class extends Templates\Base {
			public function __construct() {
				static $first = true;
				// don't fail first time to allow creating anonymous class
				if ($first) {
					$first = false;
					return;
				}
				throw new Exceptions\TemplateNotFoundException("Not found");
			}
			public function execute(array $context, mixed ...$controller_args): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};
		$this->expectException(Exceptions\TemplateNotFoundException::class);
		(new TemplateResolver($tc_fail::class))->resolve('asdf');
	}

	public function testEmptyTemplateNameThrows(): void {
		$r = new TemplateResolver();
		$this->expectException(ValueError::class);
		$r->resolve('');
	}

	public function testDefaultResolver(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		$r = new TemplateResolver();
		$t = $r->resolve($path);
		$this->assertInstanceOf(Templates\File::class, $t);
		$this->assertEquals($path, $t->path);

		fclose($fd);
	}
}
