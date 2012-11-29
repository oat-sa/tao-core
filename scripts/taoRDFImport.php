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
			'name' => 'model',
			'type' => 'string',
			'shortcut' => 'm',
			'description' => 'The target model URI. If not provided, the target model will xml:base. If no xml:base is found, the local model is used. If provided, it will override the value of xml:base.'
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