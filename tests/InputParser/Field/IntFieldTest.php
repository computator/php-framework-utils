<?php declare(strict_types=1);

use Computator\FrameworkUtils\InputParser\Field\IntField;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class IntFieldTest extends TestCase {
	#[TestDox('$value parses as $expected')]
	#[TestWith(['3', 3])]
	public function testValid(mixed $value, int $expected): void {
		$v = (new IntField())->parse($value);
		$this->assertEquals($expected, $v);
	}

	#[TestDox('$value throws')]
	#[TestWith([null])]
	#[TestWith([''])]
	#[TestWith(['3.14'])]
	public function testInvalid(mixed $value): void {
		$this->expectException(ValueError::class);
		(new IntField())->parse($value);
	}
}
