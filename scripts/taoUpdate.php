<?php
require_once '../includes/raw_start.php';

new tao_scripts_TaoUpdate(array(
	'min'		=> 0,
	'parameters' => array(
		array(
			'name' 			=> 'verbose',
			'type' 			=> 'boolean',
			'shortcut'		=> 'v',
			'description'	=> 'Verbose'
		),
		array(
			'name' 			=> 'version',
			'type' 			=> 'string',
			'shortcut'		=> 'r',
			'description'	=> 'Version to update to'
		)
	)
));
?>
