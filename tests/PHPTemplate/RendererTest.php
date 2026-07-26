<?php declare(strict_types=1);

use Computator\FrameworkUtils\PHPTemplate\FileTemplate;
use Computator\FrameworkUtils\PHPTemplate\Renderer;
use Computator\FrameworkUtils\PHPTemplate\TextTemplate;
use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase {
	public function testBasicTextTemplateRender(): void {
		$r = new Renderer(new TextTemplate('asdf'));
		$this->expectOutputString('asdf');
		$r->render();
	}

	public function testBasicFileTemplateRender(): void {
		$fd = tmpfile();
		['uri' => $path] = stream_get_meta_data($fd);

		fwrite($fd, 'asdf');

		$r = new Renderer(new FileTemplate($path));
		$this->expectOutputString('asdf');
		$r->render();

		fclose($fd);
	}
}
