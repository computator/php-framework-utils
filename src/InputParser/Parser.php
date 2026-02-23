<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser;

use Exception;
use function array_key_exists, is_int, is_string;

class Parser {
	protected array $valid_fields = [];

	public function __construct(
		public readonly array $input,
		array $fields = [],
		protected ParserConfig $parse_opts = new ParserConfig(),
		ParserConfig ...$fields2,
	) {
		foreach ([...$fields, ...$fields2] as $field => $opts) {
			if (is_int($field) && is_string($opts)) {
				$this->defineField($opts);
			} else {
				$this->defineField($field, $opts);
			}
		}
	}

	public function defineField(string $field, ?ParserConfig $field_opts = null): void {
		$this->valid_fields[$field] = $field_opts;
	}

	public function get(string $field): mixed {
		return $this->_get($field);
	}

	protected function _get(string $field): mixed {
		if (!array_key_exists($field, $this->valid_fields))
			throw new \OutOfRangeException("field '{$field}' is not defined");
		// TODO: cache result
		// TODO: store original?
		return $this->parseField(
			$this->input[$field] ?? null,
			$this->valid_fields[$field] ?? $this->parse_opts,
		);
	}

	protected function parseField(mixed $value, ParserConfig $opts): mixed {
		// basic checks
		if ($value === null)
			return null;
		if (!is_string($value))
			return $value;
		if ($opts->unset_empty && $value === '')
			return null;

		// processing
		foreach ($opts->filters as $filt)
			$value = $filt($value);

		// validation
		if ($opts->invalid_empty && $value === '')
			throw new \ValueError("invalid field: empty after filtering");

		return $value;
	}

	public function evalAll(): array {
		$out = [];
		$errs = [];
		foreach (array_keys($this->valid_fields) as $f) {
			try {
				$out[$f] = $this->get($f);
			}
			catch (Exception $e) {
				$out[$f] = null;
				$errs[$f] = $e;
			}
		}
		return [$out, $errs];
	}
}
