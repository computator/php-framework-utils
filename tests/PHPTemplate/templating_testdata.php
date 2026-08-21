<?php return [

'empty' => [
	<<<'INPUT'
	INPUT,
	[],
	'',
],

'empty PHP 8.5' => [
	<<<'INPUT'
	INPUT,
	[],
	'',
	'php_min_ver' => 8.5,
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
		'child_tpl' => <<<'DEP'
		child
		:
		DEP,
	],
	<<<'EXPECTED'
	parent before
	child
	:parent after
	EXPECTED,
],

'minimal inheritance' => [
	<<<'INPUT'
	<? self::inherit('base_tpl') ?>
	<? self::define('block1') ?>
		main block
		:
	<? self::define_end() ?>
	INPUT,
	[
		'base_tpl' => <<<'DEP'
		base beforeblock
		<? self::block('block1') ?>
		base after
		DEP,
	],
	<<<'EXPECTED'
	base beforeblock
		main block
		:
	base after
	EXPECTED,
],

/* TODO: implement
'base template' => [
	<<<'INPUT'
	main beforedep
	<? self::inherit('base_tpl') ?>
	main beforeblock
	<? self::define('block1') ?>
		main block
		:
	<? self::define_end() ?>
	main after
	INPUT,
	[
		'base_tpl' => <<<'DEP'
		base beforeblock
		<? self::block('block1') ?>
		base beforeprimary
		<? self::primary() ?>
		base after
		DEP,
	],
	<<<'EXPECTED'
	base beforeblock
		main block
		:base beforeprimary
	main beforedep
	main beforeblock
	main after
	base after
	EXPECTED,
],
*/

] ?>
