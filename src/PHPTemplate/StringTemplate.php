<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class StringTemplate extends TemplateBase {
	public function __construct(
		public readonly string $content,
	) {}

	public function get_contents(int $offset = 0, ?int $length = null): string {
		return substr($this->content, $offset, $length);
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		echo $this->content;
		return null;
	}
}
