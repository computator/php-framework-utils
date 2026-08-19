<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Templates;

interface RenderManager {
	public function getTemplateAsProxy(string $template): RenderObjects\TemplateRenderProxy;

	public function getTemplateInstanceAsProxyById(int $orig_proxy_id): ?RenderObjects\TemplateRenderProxy;

	public function renderChild(RenderObjects\TemplateRenderProxy $proxy): void;

	public function renderError(Templates\PHPString|Templates\Text|string $error): void;

	public function setParentForTemplate(Templates\Base $tpl, string $parent_template): void;

	public function startRenderingBlock(Templates\Base $tpl, string $block_name): void;

	public function endRenderingBlock(Templates\Base $tpl): void;
}
