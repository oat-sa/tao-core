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
        if($this->options['enable']){

        	//check if some config constants have to be overrided
        	$type		= !is_null($this->options['type']) 		? $this->options['type'] 	: GENERIS_VERSIONED_REPOSITORY_TYPE;
        	$url		= !is_null($this->options['url']) 		? $this->options['url'] 	: GENERIS_VERSIONED_REPOSITORY_URL;
        	$login		= !is_null($this->options['login']) 	? $this->options['login'] 	: GENERIS_VERSIONED_REPOSITORY_LOGIN;
        	$password	= !is_null($this->options['password']) 	? $this->options['password']: GENERIS_VERSIONED_REPOSITORY_PASSWORD;
        	$path		= !is_null($this->options['path']) 		? $this->options['path'] 	: GENERIS_VERSIONED_REPOSITORY_PATH;

			$repo = core_kernel_versioning_Repository::create($type, $url, $login, $password, $path, '', '');
        	try {
				//Initialize
        		$success = $repo->enable();
        		
        		if ($success) {
					self::out(__('settings updated'), array('color' => 'light_blue'));
        		} else {
					self::out(__('settings could not be updatedupdated'), array('color' => 'red'));
				}
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