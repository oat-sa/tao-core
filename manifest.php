<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
return array(
	'name' => 'tao',
	'description' => 'TAO is the meta-extension, a container for the TAOs sub extensions',
	'additional' => array(
		'version' => '2.0',
		'author' => 'CRP Henri Tudor',
		'dependances' => array('generis'),
		'models' => 'http://www.tao.lu/Ontologies/TAO.rdf',
		'install' => array( 
			'php' => dirname(__FILE__). '/install/install.php',
			'rdf' => dirname(__FILE__). '/models/ontology/tao.rdf'
		),
		'classLoaderPackages' => array( 
			dirname(__FILE__).'/actions/',
			dirname(__FILE__).'/helpers/',
			dirname(__FILE__).'/helpers/form'
		 )
	)
);
?>