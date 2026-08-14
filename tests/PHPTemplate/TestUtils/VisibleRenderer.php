<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\Test\PHPTemplate\TestUtils;

use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;

class VisibleRenderer extends Renderer {
	public RenderTree\Tree $rendertree;
}
