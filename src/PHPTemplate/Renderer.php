<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class Renderer {
	protected readonly TemplateBase $root_template;
	protected readonly TemplateResolver $resolver;
	protected bool $rendering_to_string;
	protected array $tpl_proxy_map = [];

	public function __construct(TemplateBase $template, TemplateResolver $resolver = new TemplateResolver()) {
		$this->root_template = $template;
		$this->resolver = $resolver;
	}

	public function render(): void {
		$this->rendering_to_string = false;
		$this->do_render($this->root_template);
	}

	public function renderToString(): string {
		$this->rendering_to_string = true;
		ob_start();
		$this->do_render($this->root_template);
		return ob_get_clean();
	}

	protected function do_render(TemplateBase $tpl): void {
		$tpl->execute(
			[],
			renderer: $this,
			template: $tpl,
		);
	}

	public function getTemplateAsProxy(string $template): ?TemplateRenderProxy {
		$tpl = $this->resolver->resolve($template);
		if ($tpl == null)
			return null;
		$proxy = new TemplateRenderProxy(
			$this,
			$tpl,
		);
		$this->tpl_proxy_map[$proxy->id] = $tpl;
		return $proxy;
	}

	public function renderChild(TemplateRenderProxy $proxy): void {
		$this->do_render($this->tpl_proxy_map[$proxy->id]);
	}
}
