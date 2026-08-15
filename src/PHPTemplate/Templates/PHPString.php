<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\Templates;

use Computator\FrameworkUtils\PHPTemplate\TemplateRuntimeController;

class PHPString extends Base {
	public readonly string $content;

	public function __construct(string $content) {
		$this->content = $content;
	}

	public function get_contents(int $offset = 0, ?int $length = null): string {
		return substr($this->content, $offset, $length);
	}

	public function execute(array $context, mixed ...$controller_args): mixed {
		$rc = new TemplateRuntimeController(...$controller_args);
		return (function (...$__exec_args) {
			extract($__exec_args['context']);
			unset($__exec_args['context']);
			// ending tag added to switch to HTML mode instead of starting in PHP mode
			return eval("?>{$__exec_args['content']}");
		})->call($rc, context: $context, content: $this->content);
	}
}
