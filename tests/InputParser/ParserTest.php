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
				'defs' => [],
				'subtests' => [
					'none' => [
						[],
						[],
						[],
					],
					'extra keys' => [
						['test' => 'asdf', 'qwer' => 3],
						[],
						[],
					],
					'extra numeric keys' => [
						['asdf', 3],
						[],
						[],
					],
				],
			],
			'string param' => [
				'defs' => [
					'test',
				],
				'subtests' => [
					'none' => [
						[],
						['test' => null],
						[],
					],
					'empty string' => [
						['test' => ''],
						['test' => ''],
						[],
					],
					'string value' => [
						['test' => 'asdf'],
						['test' => 'asdf'],
						[],
					],
					'extra keys' => [
						['test' => 'asdf', 'qwer' => 3, 'zxcv', 3],
						['test' => 'asdf'],
						[],
					],
				],
			],
		] as $test_name => ['defs' => $defs, 'subtests' => $subtests])
			foreach ($subtests as $subname => $subargs)
				yield "\"{$test_name}\" with input \"{$subname}\"" => [$defs, ...$subargs];
	}
}
