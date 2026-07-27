<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

class TextTemplate extends TemplateBase {
	public readonly string $content;

	public function __construct(string $content) {
		$this->content = $content;
	}

	public function get_contents(int $offset = 0, ?int $length = null): string {
		return substr($this->content, $offset, $length);
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		$rc = new TemplateRuntimeController(...$controller_args);
		return $rc->__execInClass(function () {
			extract($this->__exec_data['context']);
			unset($this->__exec_data['context']);
			// ending tag added to switch to HTML mode instead of starting in PHP mode
			return eval("?>{$this->__exec_data['content']}");
		}, ['context' => $context, 'content' => $this->content]);
	}
}
