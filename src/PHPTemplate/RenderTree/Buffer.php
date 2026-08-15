<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate\RenderTree;

use JsonSerializable;
use Stringable;

class Buffer implements Renderable, Stringable, JsonSerializable {
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

	public function __toString(): string {
		return $this->getContents();
	}

	public function jsonSerialize(): mixed {
		return $this->getContents();
	}
}
