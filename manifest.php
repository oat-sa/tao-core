<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
return array(
	'name' => 'tao',
	'description' => 'TAO is the meta-extension, a container for the TAOs sub extensions',
	'additional' => array(
		'version' => '2.3',
		'author' => 'CRP Henri Tudor',
		'dependances' => array('generis'),
		'models' => array(
			'http://www.tao.lu/Ontologies/TAO.rdf',
			'http://www.tao.lu/Ontologies/taoFuncACL.rdf'
		),
		'modelsRight' => array (
			LOCAL_NAMESPACE => '7'
		),
		'install' => array(
			'rdf' => array(
					array('ns' => 'http://www.tao.lu/Ontologies/TAO.rdf', 'file' => dirname(__FILE__). '/models/ontology/tao.rdf'),
					array('ns' => 'http://www.tao.lu/Ontologies/taoFuncACL.rdf', 'file' => dirname(__FILE__). '/models/ontology/taofuncacl.rdf'),
					array('ns' => 'http://www.tao.lu/Ontologies/taoFuncACL.rdf', 'file' => dirname(__FILE__). '/models/ontology/taoaclrole.rdf')
			)
		),
		'classLoaderPackages' => array(
			dirname(__FILE__).'/actions/',
			dirname(__FILE__).'/helpers/',
			dirname(__FILE__).'/helpers/form'
		 )
	)
);
?>