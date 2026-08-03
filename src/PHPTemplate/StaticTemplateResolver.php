<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use function array_key_exists;

class StaticTemplateResolver extends TemplateResolver {
	public function __construct(
		protected array $templates,
		string $new_template_class = Templates\PHPString::class,
	) {
		parent::__construct($new_template_class);
	}

	protected function map(string $template): Templates\Base {
		if (!array_key_exists($template, $this->templates))
			throw new Exceptions\TemplateNotFoundException("template '{$template}' not found");
		return new $this->new_template_class($this->templates[$template]);
	}
}
