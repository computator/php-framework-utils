<?php declare(strict_types=1);

use Computator\FrameworkUtils\InputParser\ParserConfig;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class ParserConfigTest extends TestCase {
	#[DoesNotPerformAssertions]
	public function testConstructorAcceptsAllProperties(): void {
		new ParserConfig(...get_class_vars(ParserConfig::class));
	}

	#[DoesNotPerformAssertions]
	public function testExtendAcceptsAllProperties(): void {
		(new ParserConfig())->extend(...get_class_vars(ParserConfig::class));
	}

	public function testExtendIgnoresNulls(): void {
		$conf = (new ParserConfig)->extend(...array_fill_keys(
			array_keys(get_class_vars(ParserConfig::class)),
			null,
		));
		$this->assertEquals(new ParserConfig, $conf);
	}

	public function testExtendChangesValues(): void {
		$orig = new ParserConfig();
		// flip a property to make them different
		$changed = $orig->extend(unset_empty: !$orig->unset_empty);
		$this->assertNotEquals($orig, $changed);
	}

	public function testExtendDoesntModifyOriginal(): void {
		$orig = new ParserConfig();
		$extended = $orig->extend();
		$this->assertNotSame($orig, $extended);
	}

	public function testExtendAppendsIterablesToListFields(): void {
		$conf = (new ParserConfig(filters: ['a', 'b']))
			->extend(filters: ['c']);
		$this->assertEquals(['a', 'b', 'c'], $conf->filters);
	}

	public function testExtendWithNonIterableForListFieldFails(): void {
		$conf = new ParserConfig(filters: ['a', 'b']);
		$this->expectException(TypeError::class);
		$conf->extend(filters: 'c');
	}
}
