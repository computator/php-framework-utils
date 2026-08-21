<?php declare(strict_types=1);

namespace Computator\FrameworkUtils\PHPTemplate;

use Computator\FrameworkUtils\PHPTemplate\Exceptions;
use Computator\FrameworkUtils\PHPTemplate\RenderTree;
use Computator\FrameworkUtils\PHPTemplate\Templates;

use ArrayAccess;
use SplObjectStorage;
use Throwable;
use ValueError;

use function array_key_exists;
use function is_string;
use function ob_get_level,ob_end_flush;

class Renderer implements RenderManager, RenderClient {
	protected readonly Templates\Base $root_template;
	protected readonly TemplateResolver $resolver;
	/** @var ArrayAccess<Templates\Base,RendererTemplateState> $tpl_state_map */
	protected SplObjectStorage $tpl_state_map;
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

		$this->tpl_state_map = new SplObjectStorage();

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
			$this->render_with_inherit($this->root_template);
		} finally {
			if ($this->using_tree)
				$this->rendertree->render();
		}
	}

	public function renderToString(): string {
		$this->rendering_to_string = true;

		ob_start();

		$this->render_with_inherit($this->root_template);
		if ($this->using_tree)
			$this->rendertree->render();

		return ob_get_clean();
	}

	protected function tpl_state(Templates\Base $tpl): RendererTemplateState {
		return $this->tpl_state_map[$tpl] ??= new RendererTemplateState();
	}

	protected function initialize_tree(): void {
		$this->using_tree = true;
		$this->rendertree = new RenderTree\Tree(
			RenderTree\Node::withValue(null),
		);
	}

	protected function swap_to_new_buffer(): bool {
		if ($was_buffering = $this->current_buff !== null)
			$this->current_buff->append(ob_get_clean());
		$this->current_buff = new RenderTree\Buffer();
		$this->rendertree->addValue($this->current_buff);
		ob_start();
		return $was_buffering;
	}

	protected function complete_buffer(): void {
		assert($this->current_buff !== null);
		$this->current_buff->append(ob_get_clean());
		$this->current_buff = null;
	}

	protected function do_render(Templates\Base $tpl): void {
		if ($this->using_tree) {
			$was_buffering = $this->swap_to_new_buffer();
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
			$this->complete_buffer();
			if ($was_buffering)
				$this->swap_to_new_buffer();
		}

		if ($error)
			throw $error;
	}

	protected function render_parent(Templates\Base $child_tpl): void	{
		$parent_tpl = $this->tpl_state($child_tpl)->parent;
		assert($parent_tpl !== null);
		assert($this->tpl_state($parent_tpl)->curr_child === null);

		try {
			$this->tpl_state($parent_tpl)->curr_child = $child_tpl;
			$this->do_render($parent_tpl);
		} finally {
			$this->tpl_state($parent_tpl)->curr_child = null;
		}

		// if the parent has it's own parent, render that
		if ($this->tpl_state($parent_tpl)->parent !== null)
			$this->render_parent($parent_tpl);
	}

	protected function render_with_inherit(Templates\Base $tpl): void {
		$this->do_render($tpl);
		if ($this->tpl_state($tpl)->parent !== null)
			$this->render_parent($tpl);
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
		$prev = $this->rendertree->getCurrentNode();
		$new_node = $this->rendertree->addNode();
		$this->rendertree->setCurrentNode($new_node);
		$this->render_with_inherit($this->tpl_proxy_map[$proxy->id]);
		$this->rendertree->setCurrentNode($prev);
		// if rendering is nested the final buffer node
		// needs to be shifted back to the parent node
		if ($this->current_buff !== null) {
			$prev->appendChildren($new_node->popChild());
		}
	}

	public function renderError(Templates\PHPString|Templates\Text|string $error): void {
		if (is_string($error))
			$error = new Templates\Text($error);
		$this->do_render($error);
	}

	public function setParentForTemplate(Templates\Base $tpl, string $parent_template): void {
		if (isset($this->tpl_state($tpl)->parent))
			throw new Exceptions\RendererStateException("template already has an associated parent");
		$this->tpl_state($tpl)->parent = $this->resolver->resolve($parent_template);
	}

	public function startRenderingBlock(Templates\Base $tpl, string $block_name): void {
		if ($block_name == '')
			throw new ValueError("'\$block_name' can not be empty");
		if ($this->tpl_state($tpl)->parent === null)
			throw new Exceptions\RendererStateException("can not define a block until a parent template has been set");
		if ($this->tpl_state($tpl)->current_block !== null)
			throw new Exceptions\RendererStateException("current block '{$this->tpl_state($tpl)->current_block}' has not been closed");
		if (isset($this->tpl_state($tpl)->blocks[$block_name]))
			throw new Exceptions\RendererStateException("can not redefine block '{$block_name}' for template");

		// swap to new IgnoredNode for block
		assert($this->tpl_state($tpl)->current_block_prev_node === null);
		$this->tpl_state($tpl)->current_block_prev_node = $this->rendertree->getCurrentNode();
		$node = $this->rendertree->addNode(RenderTree\IgnoredNode::withValue(null));
		$this->rendertree->setCurrentNode($node);

		// store current block
		$this->tpl_state($tpl)->blocks[$block_name] = $node;
		$this->tpl_state($tpl)->current_block = $block_name;

		// swap to buffer in new node
		$was_buffering = $this->swap_to_new_buffer();
		assert($was_buffering == true);
	}

	public function endRenderingBlock(Templates\Base $tpl): void {
		if ($this->tpl_state($tpl)->current_block === null)
			throw new Exceptions\RendererStateException("not currently rendering a block");

		// complete buffer for block
		$this->complete_buffer();

		// swap back to the node active before the block
		assert($this->tpl_state($tpl)->current_block_prev_node !== null);
		$this->rendertree->setCurrentNode($this->tpl_state($tpl)->current_block_prev_node);
		$this->tpl_state($tpl)->current_block_prev_node = null;

		// swap to buffer in original node
		$this->swap_to_new_buffer();

		$this->tpl_state($tpl)->current_block = null;
	}
}
