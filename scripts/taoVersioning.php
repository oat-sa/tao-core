<?php
require_once dirname(__FILE__) .'/../includes/raw_start.php';

new tao_scripts_TaoVersioning(array(
	'min'		=> 1,
	'parameters' => array(
		array(
			'name' 			=> 'enable',
			'type' 			=> 'boolean',
			'shortcut'		=> 'e',
			'description'	=> 'Enable tao versioning'
		),
		array(
			'name' 			=> 'disable',
			'type' 			=> 'boolean',
			'shortcut'		=> 'd',
			'description'	=> 'Disable tao versioning'
		),
		array(
			'name' 			=> 'type',
			'type' 			=> 'string',
			'shortcut'		=> 't',
			'description'	=> 'Type of repository (svn)'
		),
		array(
			'name' 			=> 'login',
			'type' 			=> 'string',
			'shortcut'		=> 'u',
			'description'	=> 'Login to access to the remote repository'
		),
		array(
			'name' 			=> 'password',
			'type' 			=> 'string',
			'shortcut'		=> 'p',
			'description'	=> 'Password to access to the remote repository'
		),
		array(
			'name' 			=> 'url',
			'type' 			=> 'string',
			'description'	=> 'Url of the remote repository'
		),
		array(
			'name' 			=> 'path',
			'type' 			=> 'string',
			'description'	=> 'Local location of the repository'
		)
	)
));
?>
