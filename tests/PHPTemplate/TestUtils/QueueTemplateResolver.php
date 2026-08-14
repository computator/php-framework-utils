<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\TemplateResolver;
use Computator\FrameworkUtils\PHPTemplate\Templates;

class QueueTemplateResolver extends TemplateResolver {
	protected array $tpls;

	public function __construct(Templates\Base ...$templates) {
		$this->tpls = $templates;
	}

	protected function map(string $template): Templates\Base {
		return array_shift($this->tpls);
	}
}
