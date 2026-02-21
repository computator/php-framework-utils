<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\InputParser;

use function
	array_diff_key,
	array_flip,
	array_intersect_key,
	get_defined_vars,
	trim;

class ParserConfig {
	// default configs
	public static function api(): self { static $c; $c ??= new self(
	); return $c; }

	public static function form(): self { static $c; $c ??= new self(
		unset_empty: true,
		filters: [
			trim(...),
		],
	); return $c; }

	public bool $unset_empty = false;
	public bool $invalid_empty = false;

	protected const LIST_FIELDS = ['filters'];
	public array $filters = [];

	public function __construct(... $opts) { $this->update(...$opts); }

	protected function update(
		?bool $unset_empty = null,
		?bool $invalid_empty = null,
		?array $filters = null,
	): void {
		foreach (get_defined_vars() as $k => $v) {
			if ($v !== null)
				$this->{$k} = $v;
		}
	}

	public function extend(... $opts): self {
		$n = clone $this;
		$n->update(...(array_diff_key($opts, array_flip(self::LIST_FIELDS))));
		if ($lfields = array_intersect_key($opts, array_flip(self::LIST_FIELDS))) {
			foreach ($lfields as $k => $v)
				$lfields[$k] = [...$lfields[$k], ...$v];
			$n->update(...$lfields);
		}
		return $n;
	}
}
