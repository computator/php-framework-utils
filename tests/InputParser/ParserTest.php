<?php declare(strict_types=1);

use Computator\FrameworkUtils\InputParser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase {
	#[DataProvider('parsingProvider')]
	#[TestDox('Parsing field defs $_dataName')]
	public function testParsing(array $field_defs, array $input, array $exp_parsed, array $exp_errors): void {
		$p = new Parser($input, $field_defs);
		[$parsed, $errors] = $p->evalAll();
		$this->assertEquals($exp_errors, $errors);
		$this->assertEquals($exp_parsed, $parsed);
	}

	public static function parsingProvider(): iterable {
		foreach ([
			'empty' => [
				'field_defs' => [],
				'subtests' => [
					'none' => [
						'input'      => [],
						'exp_parsed' => [],
						'exp_errors' => [],
					],
					'extra keys' => [
						'input'      => ['test' => 'asdf', 'qwer' => 3],
						'exp_parsed' => [],
						'exp_errors' => [],
					],
					'extra numeric keys' => [
						'input'      => ['asdf', 3],
						'exp_parsed' => [],
						'exp_errors' => [],
					],
				],
			],
			'string param' => [
				'field_defs' => [
					'test',
				],
				'subtests' => [
					'none' => [
						'input'      => [],
						'exp_parsed' => ['test' => null],
						'exp_errors' => [],
					],
					'empty string' => [
						'input'      => ['test' => ''],
						'exp_parsed' => ['test' => ''],
						'exp_errors' => [],
					],
					'string value' => [
						'input'      => ['test' => 'asdf'],
						'exp_parsed' => ['test' => 'asdf'],
						'exp_errors' => [],
					],
					'extra keys' => [
						'input'      => ['test' => 'asdf', 'qwer' => 3, 'zxcv', 3],
						'exp_parsed' => ['test' => 'asdf'],
						'exp_errors' => [],
					],
				],
			],
		] as $test_name => ['field_defs' => $defs, 'subtests' => $subtests])
			foreach ($subtests as $subname => $subargs)
				yield "\"{$test_name}\" with input \"{$subname}\"" => [$defs, ...$subargs];
	}
}
