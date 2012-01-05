<?php
require_once dirname(__FILE__) .'/../includes/raw_start.php';

new tao_scripts_TaoTranslate(array(
	'min' => 0,
	'parameters' => array(
		array(
			'name' => 'verbose',
			'type' => 'boolean',
			'shortcut' => 'v',
			'description' => 'Verbose mode'
		),
		array(
			'name' => 'action',
			'type' => 'string',
			'shortcut' => 'a',
			'description' => 'Action to undertake like Create, Update, Delete, ...'
		)
	)
));
?>