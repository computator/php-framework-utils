<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RendererTemplateState;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Templates;

class VisibleRenderer extends Renderer {
	public RenderTree\Tree $rendertree;

	public function tpl_state(Templates\Base $tpl): RendererTemplateState {
		return parent::tpl_state($tpl);
	}

	public function swap_to_new_buffer(): bool {
		return parent::swap_to_new_buffer();
	}

	public function complete_buffer(): void {
		parent::complete_buffer();
	}
}
