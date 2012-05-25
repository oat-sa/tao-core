<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.TaoVersioning.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 13.10.2011, 07:52:44 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F57-includes begin
// section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F57-includes end

/* user defined constants */
// section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F57-constants begin
// section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F57-constants end

/**
 * Short description of class tao_scripts_TaoVersioning
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoVersioning
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public function preRun()
    {
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F58 begin
        $this->options = array (
    		'enable'	=> false,
    		'disable'	=> false,
        	'login'		=> null,
        	'password'	=> null,
        	'type'		=> null,
        	'url'		=> null,
        	'path'		=> null
    	);
    	$this->options = array_merge($this->options, $this->parameters);
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F58 end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public function run()
    {
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F5A begin
        
    	$repositoryType = '';
        if($this->options['enable']){
        	
        	//check if some config constants have to be overrided
        	$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'] 	= !is_null($this->options['login']) 	? $this->options['login'] 	: GENERIS_VERSIONED_REPOSITORY_LOGIN;
        	$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'] = !is_null($this->options['password']) 	? $this->options['password']: GENERIS_VERSIONED_REPOSITORY_PASSWORD;
        	$constants['GENERIS_VERSIONED_REPOSITORY_TYPE'] 	= !is_null($this->options['type']) 		? $this->options['type'] 	: GENERIS_VERSIONED_REPOSITORY_TYPE;
        	$constants['GENERIS_VERSIONED_REPOSITORY_URL'] 		= !is_null($this->options['url']) 		? $this->options['url'] 	: GENERIS_VERSIONED_REPOSITORY_URL;
        	$constants['GENERIS_VERSIONED_REPOSITORY_PATH'] 	= !is_null($this->options['path']) 		? $this->options['path'] 	: GENERIS_VERSIONED_REPOSITORY_PATH;
        	$constants['GENERIS_VERSIONING_ENABLED'] 			= true;
        	
	        //update the generis config file with the new constants
	        $configWriter = new tao_install_utils_ConfigWriter(GENERIS_BASE_PATH.'/common/conf/sample/versioning.conf.php', GENERIS_BASE_PATH.'/common/conf/versioning.conf.php');
	        $configWriter->writeConstants($constants);
        	
        	//Regarding to the versioning sytem type
        	switch($constants['GENERIS_VERSIONED_REPOSITORY_TYPE']){
        		case 'svn':
        			$repositoryType = 'http://www.tao.lu/Ontologies/TAOItem.rdf#VersioningRepositoryTypeSubversion';
        			break;
        		default:
					self::out("Unable to recognize the given type ".$constants['GENERIS_VERSIONED_REPOSITORY_TYPE'], array('color' => 'red'));
        			return;
        	}
        	
        	$repositoryExist = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository');
        	if($repositoryExist->exists()){
				self::out("Warning : The default repository (http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository) exists ", array('color' => 'light_red'));
				self::out("It will be replaced by the new one", array('color' => 'light_red'));
        	}
        	
        	// Instantiate the repository in the ontology
	        $repository = core_kernel_versioning_Repository::create(
				new core_kernel_classes_Resource($repositoryType),
				$constants['GENERIS_VERSIONED_REPOSITORY_URL'],
				$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'],
				$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'],
				$constants['GENERIS_VERSIONED_REPOSITORY_PATH'],
				GENERIS_VERSIONED_REPOSITORY_LABEL,
				GENERIS_VERSIONED_REPOSITORY_COMMENT,
				'http://www.tao.lu/Ontologies/generis.rdf#DefaultRepository'
			);
			
			// Checkout the repository
			if (!is_null($repository)){
				
				//bypass the repository object because of loaded constants
				if(!core_kernel_versioning_RepositoryProxy::singleton()->authenticate($repository, $constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'], $constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'])){
					self::out("Unable to reach the remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." ".$constants['GENERIS_VERSIONED_REPOSITORY_LOGIN'].":".$constants['GENERIS_VERSIONED_REPOSITORY_PASSWORD'], array('color' => 'light_red'));
					self::out("Please check your configuration");
					return;
				}
				else {
					if(core_kernel_versioning_RepositoryProxy::singleton()->checkout($repository, $constants['GENERIS_VERSIONED_REPOSITORY_URL'], $constants['GENERIS_VERSIONED_REPOSITORY_PATH'])){
						self::out("The remote versioning repository ".$constants['GENERIS_VERSIONED_REPOSITORY_URL']." is bound to TAO", array('color' => 'light_blue'));
						self::out("local directory : ".$constants['GENERIS_VERSIONED_REPOSITORY_PATH']);
					} else {
						self::out('Unable to checkout the remote repository '.$constants['GENERIS_VERSIONED_REPOSITORY_URL'], array('color' => 'red'));
						return;
					}
					
				}
			}
        }
    	
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F5A end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public function postRun()
    {
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F5C begin
        // section 127-0-1-1--33cecc33:132fbb6bd64:-8000:0000000000003F5C end
    }

} /* end of class tao_scripts_TaoVersioning */

?>