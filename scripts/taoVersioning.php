<?php
require_once '../includes/raw_start.php';

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
		)
	)
));
?>
