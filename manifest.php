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
				array('type' => 'CheckPHPRuntime', 'value' => array('id' => 'tao_php_runtime', 'min' => '5.3')),
				array('type' => 'CheckPHPRuntime', 'value' => array('id' => 'tao_php_runtime53', 'min' => '5.3', 'max' => '5.3.x', 'silent' => true)),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_pdo', 'name' => 'PDO')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_curl', 'name' => 'curl')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_zip', 'name' => 'zip')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_json', 'name' => 'json')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_spl', 'name' => 'spl')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_dom', 'name' => 'dom')),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_mbstring', 'name' => 'mbstring')),
				//array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_svn', 'name' => 'svn', 'optional' => true)),
				array('type' => 'CheckPHPExtension', 'value' => array('id' => 'tao_extension_suhosin', 'name' => 'suhosin', 'silent' => true)),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_magic_quotes_gpc', 'name' => 'magic_quotes_gpc', 'value' => '0', 'dependsOn' => array('tao_php_runtime53'))),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_register_globals', 'name' => 'register_globals', 'value' => '0', 'dependsOn' => array('tao_php_runtime53'))),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_short_open_tag', 'name' => 'short_open_tag', 'value' => '1')),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_safe_mode', 'name' => 'safe_mode', 'value' => '0', 'dependsOn' => array('tao_php_runtime53'))),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_suhosin_post_max_name_length', 'name' => 'suhosin.post.max_name_length', 'value' => '128', 'dependsOn' => array('tao_extension_suhosin'))),
				array('type' => 'CheckPHPINIValue', 'value' => array('id' => 'tao_ini_suhosin_request_max_varname_length', 'name' => 'suhosin.request.max_varname_length', 'value' => '128', 'dependsOn' => array('tao_extension_suhosin'))),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_root', 'location' => '.', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_data_cache', 'location' =>  'generis/data/cache', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_data_versionning', 'location' => 'generis/data/versioning', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_common', 'location' => 'generis/common', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_common_conf', 'location' => 'generis/common/conf', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_common_conf_default', 'location' => 'generis/common/conf/default', 'rights' => 'r')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_generis_common_conf_sample', 'location' => 'generis/common/conf/sample', 'rights' => 'r')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_views_export', 'location' => 'tao/views/export', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_includes', 'location' => 'tao/includes', 'rights' => 'r')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_data_cache', 'location' => 'tao/data/cache', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_update_patches', 'location' => 'tao/update/patches', 'rights' => 'rw')),
				array('type' => 'CheckFileSystemComponent', 'value' => array('id' => 'fs_tao_locales', 'location' => 'tao/locales', 'rights' => 'r')),
				array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_mod_rewrite', 'name' => 'mod_rewrite', 'extension' => 'tao')),
				array('type' => 'CheckCustom', 'value' => array('id' => 'tao_custom_database_drivers', 'name' => 'database_drivers', 'extension' => 'tao'))
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