<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\StaticTemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\Templates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticTemplateResolver::class)]
final class StaticTemplateResolverTest extends TestCase {
	public function testTemplateMappingFound(): void {
		$tc_success = new class ('') extends Templates\Base {
			public function __construct(
				public readonly string $value,
			) {}
			public function execute(array $context, mixed ...$controller_args): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};

		$resolved = (new StaticTemplateResolver(
			[
				'asdf' => 'one',
				'qwer' => 'two',
			],
			$tc_success::class,
		))->resolve('asdf');
		$this->assertInstanceOf(Templates\Base::class, $resolved);
		$this->assertEquals('one', $resolved->value);
	}

	public function testTemplateMappingNotFound(): void {
		$tc_success = new class ('') extends Templates\Base {
			public function __construct(
				public readonly string $value,
			) {}
			public function execute(array $context, mixed ...$controller_args): mixed {
				return null;
			}
			public function get_contents(int $offset = 0, int|null $length = null): string {
				return "";
			}
		};

		$this->expectException(Exceptions\TemplateNotFoundException::class);
		(new StaticTemplateResolver(
			[
				'asdf' => 'one',
				'qwer' => 'two',
			],
			$tc_success::class,
		))->resolve('invalid');
	}

	public function testDefaultTemplateClass(): void {
		$resolved = (new StaticTemplateResolver([
			'asdf' => 'one',
			'qwer' => 'two',
		]))->resolve('asdf');
		$this->assertInstanceOf(Templates\PHPString::class, $resolved);
		$this->assertEquals('one', $resolved->content);
	}
}
