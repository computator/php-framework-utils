<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

interface RenderClient {
	public function render(): void;

	public function renderToString(): string;
}
