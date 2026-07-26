<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TextTemplate extends TemplateBase {
	public readonly string $content;

	public function __construct(string $content) {
		$this->content = $content;
	}

	protected function get_contents(int $offset = 0, ?int $length = null): string {
		return substr($this->content, $offset, $length);
	}

	public function execute(array $__context): mixed {
		extract($__context);
		// ending tag added to switch to HTML mode instead of starting in PHP mode
		return eval("?>{$this->content}");
	}
}
