<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Exception;
use TypeError;
use ValueError;

class TemplateResolver {
	public function __construct(
		protected string $new_template_class = FileTemplate::class,
	) {
		if (!is_a($new_template_class, TemplateBase::class, true))
			throw new TypeError("'\$new_template_class' must be an instance of TemplateBase");
	}

	public function resolve(string $template): ?TemplateBase {
		if ($template == '')
			throw new ValueError("'\$template' can not be empty");
		try {
			return new $this->new_template_class($template);
		} catch (Exception) {
			return null;
		}
	}
}
