<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use function array_key_exists;
use function is_string;

class Renderer {
	protected readonly Templates\Base $root_template;
	protected readonly TemplateResolver $resolver;
	protected bool $rendering_to_string;
	protected array $tpl_proxy_map = [];

	public function __construct(Templates\Base $template, TemplateResolver $resolver = new TemplateResolver()) {
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

	protected function do_render(Templates\Base $tpl): void {
		// TODO: handle execute return values
		$tpl->execute(
			[],
			renderer: $this,
			template: $tpl,
		);
	}

	public function getTemplateAsProxy(string $template): RenderObjects\TemplateRenderProxy {
		$tpl = $this->resolver->resolve($template);
		$proxy = new RenderObjects\TemplateRenderProxy(
			$this,
			$tpl,
		);
		$this->tpl_proxy_map[$proxy->id] = $tpl;
		return $proxy;
	}

	public function getTemplateInstanceAsProxyById(int $orig_proxy_id): ?RenderObjects\TemplateRenderProxy {
		if (!array_key_exists($orig_proxy_id, $this->tpl_proxy_map))
			return null;
		$proxy = new RenderObjects\TemplateRenderProxy(
			$this,
			$this->tpl_proxy_map[$orig_proxy_id],
		);
		$this->tpl_proxy_map[$proxy->id] = $this->tpl_proxy_map[$orig_proxy_id];
		return $proxy;
	}

	public function renderChild(RenderObjects\TemplateRenderProxy $proxy): void {
		$this->do_render($this->tpl_proxy_map[$proxy->id]);
	}

	public function renderError(Templates\PHPString|Templates\Text|string $error): void {
		if (is_string($error))
			$error = new Templates\Text($error);
		$this->do_render($error);
	}
}
