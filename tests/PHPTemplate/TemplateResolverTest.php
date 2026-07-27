<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\FileTemplate;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\TemplateBase;
use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\TextTemplate;
use PHPUnit\Framework\TestCase;

final class TemplateResolverTest extends TestCase {
	public function testNewClassMustBeTemplateClass(): void {
		$this->expectException(TypeError::class);
		new TemplateResolver(stdClass::class);
	}

	public function testTemplateFoundReturnsTemplate(): void {
		$tc_success = new class extends TemplateBase {
			public function __construct() {}
			public function execute(mixed ...$__context): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};

		$resolved = (new TemplateResolver($tc_success::class))->resolve('asdf');
		$this->assertInstanceOf($tc_success::class, $resolved);
	}

	public function testTemplateNotFoundReturnsNull(): void {
		$tc_fail = new class extends TemplateBase {
			public function __construct() {
				static $first = true;
				// don't fail first time to allow creating anonymous class
				if ($first) {
					$first = false;
					return;
				}
				throw new Exception("Not found");
			}
			public function execute(mixed ...$__context): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};
		$resolved = (new TemplateResolver($tc_fail::class))->resolve('asdf');
		$this->assertNull($resolved);
	}

	public function testEmptyTemplateNameThrows(): void {
		$r = new TemplateResolver();
		$this->expectException(ValueError::class);
		$t = $r->resolve('');
	}

	public function testDefaultResolver(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		$r = new TemplateResolver();
		$t = $r->resolve($path);
		$this->assertInstanceOf(FileTemplate::class, $t);
		$this->assertEquals($path, $t->path);

		fclose($fd);
	}
}
