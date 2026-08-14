<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Templates;

use SplObjectStorage;
use Throwable;

use function array_key_exists;
use function is_string;
use function ob_get_level,ob_end_flush;

class Renderer implements RenderManager, RenderClient {
	protected readonly Templates\Base $root_template;
	protected readonly TemplateResolver $resolver;
	protected SplObjectStorage $tpl_parent_map;
	protected array $tpl_proxy_map = [];

	protected readonly bool $using_tree;
	protected RenderTree\Tree $rendertree;
	protected ?RenderTree\Node $current_node = null;
	protected ?RenderTree\Buffer $current_buff = null;

	protected bool $rendering_to_string;

	public static function create(Templates\Base $template, TemplateResolver $resolver = new TemplateResolver()): RenderClient {
		return new static($template, $resolver);
	}

	final private function __construct(Templates\Base $template, TemplateResolver $resolver) {
		$this->root_template = $template;
		$this->resolver = $resolver;

		$this->tpl_parent_map = new SplObjectStorage();

		// TODO: make using a tree optional once we can detect if
		// there is any out of order rendering, or always use the tree
		// if we can render the tree without any buffering
		$this->initialize_tree();
	}

	public function render(): void {
		$this->rendering_to_string = false;

		// use try-finally since we want to send output as well
		// as throw any errors
		try {
			$this->do_render($this->root_template);
		} finally {
			if ($this->using_tree)
				$this->rendertree->render();
		}
	}

	public function renderToString(): string {
		$this->rendering_to_string = true;

		ob_start();

		$this->do_render($this->root_template);
		if ($this->using_tree)
			$this->rendertree->render();

		return ob_get_clean();
	}

	protected function initialize_tree(): void {
		$this->using_tree = true;
		$this->rendertree = new RenderTree\Tree(
			RenderTree\Node::withValue(null),
		);
	}

	protected function do_render(Templates\Base $tpl): void {
		if ($this->using_tree) {
			if ($was_buffering = $this->current_buff !== null)
				$this->current_buff->append(ob_get_clean());
			$this->current_buff = new RenderTree\Buffer();
			$this->rendertree->addValue($this->current_buff);
			ob_start();
		}
		$start_ob_level = ob_get_level();
		$error = null;

		try {
			// TODO: handle execute return values
			$tpl->execute(
				[],
				renderer: $this,
				template: $tpl,
			);
		} catch (Throwable $t) {
			$error = new Exceptions\TemplateRenderException(
				"error while rendering template: {$t->getMessage()}",
				previous: $t,
			);
		}

		if (ob_get_level() > $start_ob_level) {
			do {
				ob_end_flush();
			} while (ob_get_level() > $start_ob_level);

			if (!$error)
				$error = new Exceptions\TemplateRenderException("template did not close it's output buffers!");
		}

		if ($this->using_tree) {
			$this->current_buff->append(ob_get_clean());
			if ($was_buffering) {
				$this->current_buff = new RenderTree\Buffer();
				$this->rendertree->addValue($this->current_buff);
				ob_start();
			}
			else
				$this->current_buff = null;
		}

		if ($error)
			throw $error;
	}

	public function getTemplateAsProxy(string $template): RenderObjects\TemplateRenderProxy {
		$tpl = $this->resolver->resolve($template);
		$proxy = new RenderObjects\TemplateRenderProxy(
			$this,
			$tpl,
		);
		$this->tpl_proxy_map[$proxy->id] = $tpl;
		return $proxy;
	}

	public function getTemplateInstanceAsProxyById(int $orig_proxy_id): ?RenderObjects\TemplateRenderProxy {
		if (!array_key_exists($orig_proxy_id, $this->tpl_proxy_map))
			return null;
		$proxy = new RenderObjects\TemplateRenderProxy(
			$this,
			$this->tpl_proxy_map[$orig_proxy_id],
		);
		$this->tpl_proxy_map[$proxy->id] = $this->tpl_proxy_map[$orig_proxy_id];
		return $proxy;
	}

	public function renderChild(RenderObjects\TemplateRenderProxy $proxy): void {
		$this->do_render($this->tpl_proxy_map[$proxy->id]);
	}

	public function renderError(Templates\PHPString|Templates\Text|string $error): void {
		if (is_string($error))
			$error = new Templates\Text($error);
		$this->do_render($error);
	}

	public function setParentForTemplate(Templates\Base $tpl, string $parent_template): void {
		if (isset($this->tpl_parent_map[$tpl]))
			throw new Exceptions\RendererException("invalid state: 'tpl' already has an associated parent");
		$this->tpl_parent_map[$tpl] = $this->resolver->resolve($parent_template);
	}
}
