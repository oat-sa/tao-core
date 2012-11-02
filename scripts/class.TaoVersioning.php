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

        	try {
						//Initialize
						tao_helpers_Versioning::initialize($constants);
						self::out(__('settings updated'), array('color' => 'light_blue'));
					} catch (Exception $e) {
						self::out($e->getMessage(), array('color' => 'red'));
						$this->setData('message', $e->getMessage());
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