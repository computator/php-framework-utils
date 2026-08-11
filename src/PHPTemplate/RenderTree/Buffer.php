<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

class Buffer implements Renderable {
	public function __construct(
		protected string $value = '',
	) {}

	public function getContents(): string {
		return $this->value;
	}

	public function append(string $data): void {
		$this->value .= $data;
	}

	public function render(): void {
		echo $this->value;
	}
}
