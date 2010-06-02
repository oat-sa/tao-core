<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */


	return array(
		'name' => 'TAO',
		'description' => 'TAO http://www.tao.lu',
		'additional' => array(
			'version' => '1.2',
			'author' => 'CRP Henri Tudor',
			'dependances' => array(),
			'install' => array( 
				'sql' => dirname(__FILE__). '/install/db/tao.sql',
				'php' => dirname(__FILE__). '/install/install.php'
			),
			
			'model' => array(
				'http://www.w3.org/2000/01/rdf-schema',
				'http://www.w3.org/1999/02/22-rdf-syntax-ns',
				'http://www.tao.lu/datatypes/WidgetDefinitions.rdf',
				'http://www.tao.lu/Ontologies/generis.rdf',
				'http://www.tao.lu/Ontologies/TAO.rdf',
				'http://www.tao.lu/middleware/hyperclass.rdf'
			),
			'classLoaderPackages' => array( 
				dirname(__FILE__).'/actions/',
				dirname(__FILE__).'/helpers/',
				dirname(__FILE__).'/helpers/form'
			 )
			

				
			
		)
	);
?>