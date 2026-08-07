<?php return [

'empty' => [
	<<<'INPUT'
	INPUT,
	[],
	'',
],

'basic text' => [
	<<<'INPUT'
	asdf
	INPUT,
	[],
	'asdf',
],

'basic code' => [
	<<<'INPUT'
	<?php
	echo "asdf";
	INPUT,
	[],
	'asdf',
],

'child template' => [
	<<<'INPUT'
	parent before
	<? self::tpl('child_tpl')() ?>
	parent after
	INPUT,
	[
		'child_tpl' => <<<'CHILD'
		child
		:
		CHILD,
	],
	<<<'EXPECTED'
	parent before
	child
	:parent after
	EXPECTED,
],

] ?>
