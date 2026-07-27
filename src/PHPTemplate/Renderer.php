<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class Renderer {
	protected readonly TemplateBase $root_template;
	protected readonly TemplateResolver $resolver;
	protected bool $rendering_to_string;

	public function __construct(TemplateBase $template, TemplateResolver $resolver = new TemplateResolver()) {
		$this->root_template = $template;
		$this->resolver = $resolver;
	}

	public function render(): void {
		$this->rendering_to_string = false;
		$this->do_render();
	}

	public function renderToString(): string {
		$this->rendering_to_string = true;
		ob_start();
		$this->do_render();
		return ob_get_clean();
	}

	protected function do_render(): void {
		$this->root_template->execute([]);
	}
}
