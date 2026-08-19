<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Templates;

class RendererTemplateState {
	public ?Templates\Base $parent = null;
	/** @var array<RenderTree\Node> $blocks */
	public array $blocks = [];
	public ?string $current_block = null;
	public ?RenderTree\Node $current_block_prev_node = null;
}
