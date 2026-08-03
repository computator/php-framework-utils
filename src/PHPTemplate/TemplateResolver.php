<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use TypeError;
use ValueError;

class TemplateResolver {
	public function __construct(
		protected string $new_template_class = Templates\File::class,
	) {
		if (!is_a($new_template_class, Templates\Base::class, true))
			throw new TypeError("'\$new_template_class' must be an instance of Templates\Base");
	}

	final public function resolve(string $template): Templates\Base {
		if ($template == '')
			throw new ValueError("'\$template' can not be empty");
		return $this->map($template);
	}

	protected function map(string $template): Templates\Base {
		return new $this->new_template_class($template);
	}
}
