<?php
require_once dirname(__FILE__) .'/../includes/raw_start.php';

// this script is deprecated and only used by the buildServer

new tao_scripts_TaoVersioning(array(
	'min'		=> 5,
	'parameters' => array(
		array(
			'name' 			=> 'enable',
			'type' 			=> 'boolean',
			'shortcut'		=> 'e',
			'description'	=> 'Enable tao versioning',
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
			'description'	=> 'Login to access to the remote repository',
			'required'		=> true
		),
		array(
			'name' 			=> 'password',
			'type' 			=> 'string',
			'shortcut'		=> 'p',
			'description'	=> 'Password to access to the remote repository',
			'required'		=> true
		),
		array(
			'name' 			=> 'url',
			'type' 			=> 'string',
			'description'	=> 'Url of the remote repository',
			'required'		=> true
		),
		array(
			'name' 			=> 'path',
			'type' 			=> 'string',
			'description'	=> 'Local location of the repository',
			'required'		=> true
		)
	)
));
?>
