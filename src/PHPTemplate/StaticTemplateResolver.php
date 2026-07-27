<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Exception;
use RuntimeException;
use TypeError;
use ValueError;

use function array_key_exists;

class StaticTemplateResolver extends TemplateResolver {
	public function __construct(
		protected array $templates,
		string $new_template_class = TextTemplate::class,
	) {
		parent::__construct($new_template_class);
	}

	protected function map(string $template): TemplateBase {
		if (!array_key_exists($template, $this->templates))
			throw new RuntimeException("template not found");
		return new $this->new_template_class($this->templates[$template]);
	}
}
