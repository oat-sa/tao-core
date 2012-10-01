<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
$extpath = dirname(__FILE__).DIRECTORY_SEPARATOR;

return array(
	'name' => 'tao',
	'description' => 'TAO is the meta-extension, a container for the TAOs sub extensions',
	'version' => '2.4',
	'author' => 'CRP Henri Tudor',
	'dependencies' => array('generis'),
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
	 ),
	 'constants' => array(
	
		# actions directory
		"DIR_ACTIONS" => $extpath."actions".DIRECTORY_SEPARATOR,
	
		# models directory
		"DIR_MODELS" => $extpath."models".DIRECTORY_SEPARATOR,
	
		# views directory
		"DIR_VIEWS" => $extpath."views".DIRECTORY_SEPARATOR,
	
		# helpers directory
		"DIR_HELPERS" => $extpath."helpers".DIRECTORY_SEPARATOR,

	 	#path to the cache
		'CACHE_PATH' => $extpath."data".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR,
	
		# default module name
		'DEFAULT_MODULE_NAME' => 'Main',
	
		#default action name
		'DEFAULT_ACTION_NAME' => 'index',
	
		#BASE PATH: the root path in the file system (usually the document root)
		'BASE_PATH' => $extpath,
	
		#BASE URL (usually the domain root)
		'BASE_URL' => ROOT_URL.'tao/',
	
		#BASE WWW the web resources path
		'BASE_WWW' => ROOT_URL . 'tao/views/',
	 
	 	#TPL PATH the path to the templates
	 	'TPL_PATH'	=> $extpath."views".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR,
	
		#STUFF that belongs in TAO
		'TAOBASE_WWW' => ROOT_URL . 'tao/views/',
		'TAO_TPL_PATH' => $extpath."views".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR,
		'TAOVIEW_PATH' => $extpath."views".DIRECTORY_SEPARATOR,
	
		#export resources path
		'EXPORT_PATH' => 	$extpath."views".DIRECTORY_SEPARATOR."export".DIRECTORY_SEPARATOR
	 )
);
?>