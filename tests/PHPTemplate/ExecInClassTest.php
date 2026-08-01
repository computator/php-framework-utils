<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\ExecInClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecInClass::class)]
final class ExecInClassTest extends TestCase {
	public function testFuncCalledInClassContextWithData(): void {
		$tc = new class {
			use ExecInClass;

			private const CLASS_CONST = 'abcd';
			private string $class_var = 'efgh';
		};

		$test_inst = $this;
		$func_called = false;
		$tc->__execInClass(
			function () use ($test_inst, &$func_called) {
				$func_called = true;
				$test_inst::assertEquals('abcd', self::CLASS_CONST);
				$test_inst::assertEquals('efgh', $this->class_var);
				$test_inst::assertEquals(1234, $this->__exec_data['data_val']);
			},
			['data_val' => 1234],
		);
		$this->assertTrue($func_called);
	}
}
