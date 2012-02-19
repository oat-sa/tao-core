<?php
require_once dirname(__FILE__) .'/../includes/raw_start.php';

new tao_scripts_TaoRDFImport(array(
	'min' => 3,
	'parameters' => array(
		array(
			'name' => 'verbose',
			'type' => 'boolean',
			'shortcut' => 'v',
			'description' => 'Verbose mode'
		),
		array(
			'name' => 'user',
			'type' => 'string',
			'shortcut' => 'u',
			'description' => 'Generis User (must be a TAO Manager)'
		),
		array(
			'name' => 'password',
			'type' => 'string',
			'shortcut' => 'p',
			'description' => 'Generis Password'
		),
		array(
			'name' => 'namespace',
			'type' => 'string',
			'shortcut' => 'n',
			'description' => 'The target namespace. If not provided, the target namespace will be the local one'
		),
		array(
			'name' => 'input',
			'type' => 'file',
			'shortcut' => 'i',
			'description' => 'The canonical path to the RDF input file to import'
		)
	)
));
?>