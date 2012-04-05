<?php
require_once dirname(__FILE__) .'/../includes/raw_start.php';

new tao_scripts_TaoTranslate(array(
	'min' => 1,
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
			'description' => 'Action to undertake. Available actions are create, update, updateAll, delete, deleteAll'
		),
		array(
			'name' => 'language',
			'type' => 'string',
			'shortcut' => 'l',
			'description' => 'A language identifier like en-US, be-NL, FR, ...'
		),
		array(
			'name' => 'output',
			'type' => 'string',
			'shortcut' => 'o',
			'description' => 'An output directory (PO and JS files)'
		),
		array(
			'name' => 'input',
			'type' => 'string',
			'shortcut' => 'i',
			'description' => 'An input directory (source code)'
		),
		array(
			'name' => 'build',
			'type' => 'boolean',
			'shortcut' => 'b',
			'description' => 'Sets if the language has to be built when created or not'
		),
		array(
			'name' => 'force',
			'type' => 'boolean',
			'shortcut' => 'f',
			'description' => 'Force to erase an existing language if you use the create action'
		),
		array(
			'name' => 'extension',
			'type' => 'string',
			'shortcut' => 'e',
			'description' => 'The TAO extension for which the script will apply'
		),
		array(
            'name' => 'ontology',
            'type' => 'boolean',
            'shortcut' => 'o',
            'description' => 'Tells to the script if it should deal with the language in the ontology'
        ),
        array(
            'name' => 'languageLabel',
            'type' => 'string',
            'shortcut' => 'll',
            'description' => 'Language label to use when creating a new language'
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
        )
	)
));
?>