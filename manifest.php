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
		),
		'checks' => array(
				array('type' => 'CheckPHPRuntime', 'value' => array('min' => '5.3', 'max' => '5.3.18')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'PDO')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'curl')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'zip')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'json')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'spl')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'dom')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'tidy')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'mbstring')),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'svn', 'optional' => true)),
				array('type' => 'CheckPHPExtension', 'value' => array('name' => 'suhosin', 'optional' => true)),
				array('type' => 'CheckPHPINIValue', 'value' => array('name' => 'magic_quotes_gpc', 'value' => "0")),
				array('type' => 'CheckPHPINIValue', 'value' => array('name' => 'short_open_tag', 'value' => "1")),
				array('type' => 'CheckPHPINIValue', 'value' => array('name' => 'register_globals', 'value' => "0")),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => '.', 'rights' => 'rw', 'name' => 'fs_root')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' =>  'generis/data/cache', 'rights' => 'rw', 'name' => 'fs_generis_data_cache')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'generis/data/versioning', 'rights' => 'rw', 'name' => 'fs_generis_data_versionning')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'generis/common', 'rights' => 'rw', 'name' => 'fs_generis_common')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'generis/common/conf', 'rights' => 'rw', 'name' => 'fs_generis_common_conf')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'generis/common/conf/default', 'rights' => 'r', 'name' => 'fs_generis_common_conf_default')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'generis/common/conf/sample', 'rights' => 'r', 'name' => 'fs_generis_common_conf_sample')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'filemanager/views/data', 'rights' => 'rw', 'name' => 'fs_filemanager_views_data')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'filemanager/includes', 'rights' => 'r', 'name' => 'fs_filemanager_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/views/export', 'rights' => 'rw', 'name' => 'fs_tao_views_export')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/includes', 'rights' => 'r', 'name' => 'fs_tao_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/data/cache', 'rights' => 'rw', 'name' => 'fs_tao_data_cache')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/update/patches', 'rights' => 'rw', 'name' => 'fs_tao_update_patches')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/update/bash', 'rights' => 'r', 'name' => 'fs_tao_update_bash')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'tao/locales', 'rights' => 'r', 'name' => 'fs_tao_locales')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoItems/data', 'rights' => 'rw', 'name' => 'fs_taoItems_data')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoItems/includes', 'rights' => 'r', 'name' => 'fs_taoItems_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoItems/views/runtime', 'rights' => 'rw', 'name' => 'fs_taoItems_views_runtime')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoDelivery/compiled', 'rights' => 'rw', 'name' => 'fs_taoDelivery_compiled')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoDelivery/includes', 'rights' => 'r', 'name' => 'fs_taoDelivery_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoGroups/includes', 'rights' => 'r', 'name' => 'fs_taoGroups_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoSubjects/includes', 'rights' => 'r', 'name' => 'fs_taoSubjects_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoTests/includes', 'rights' => 'r', 'name' => 'fs_taoTests_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'taoResults/includes', 'rights' => 'r', 'name' => 'fs_taoResults_includes')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('location' => 'wfEngine/includes', 'rights' => 'r', 'name' => 'fs_wfEngine_includes')),
				array('type' => 'CheckCustom', 'value' => array('name' => 'mod_rewrite', 'extension' => 'tao')),
				array('type' => 'CheckCustom', 'value' => array('name' => 'database_drivers', 'extension' => 'tao'))
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