<?php

error_reporting(E_ALL);

/**
 * TAO - tao\scripts\class.TaoRDFImport.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 16.02.2012, 14:15:18 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CD-includes begin
// section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CD-includes end

/* user defined constants */
// section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CD-constants begin
// section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CD-constants end

/**
 * Short description of class tao_scripts_TaoRDFImport
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoRDFImport
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method preRun
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function preRun()
    {
        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CE begin
        $this->options = array('verbose' => false,
        					   'user' => null,
        					   'password' => null,
        					   'targetNamespace' => null,
        					   'input' => null);
        
        $this->options = array_merge($this->options, $this->parameters);
        
        if ($this->options['user'] == null){
        	self::err("Please provide a TAO 'user'.", true);
        }
        else if ($this->options['password'] == null){
        	self::err("Please provide a TAO 'password'.", true);
        }
        else if ($this->options['input'] == null){
        	self::err("Please provide a RDF 'input' file.", true);
        }

        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037CE end
    }

    /**
     * Short description of method run
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function run()
    {
        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037D0 begin
        $userService = tao_models_classes_UserService::singleton();
        $this->outVerbose("Connecting to TAO as '" . $this->options['user'] . "' ...");
        if ($userService->loginUser($this->options['user'], md5($this->options['password']))){
        	$this->outVerbose("Connected to TAO as '" . $this->options['user'] . "'.");
        	
        	//get the session & determine the target namespace.
        	$session = core_kernel_classes_Session::singleton();
        	$targetNamespace = $session->getNameSpace();
        	if (isset($this->options['namespace']) && !is_null($this->options['namespace'])){
        		$targetNamespace = $this->options['namespace'];
        	}
			
        	//validate the file to import
			$parser = new tao_models_classes_Parser($this->options['input'],
													array('extension' => 'rdf'));
			$parser->validate();
			
			if(!$parser->isValid()){
				foreach ($parser->getErrors() as $error) {
					$this->outVerbose("RDF-XML parsing error in '" . $error['file'] . "' at line '" . $error['line'] . "': '" . $error['message']. "'.");
				}
				
				$userService->logout();
				self::err("RDF-XML parsing error.", true);
			}
			else{
			
				//initialize the adapter (no target class but a target namespace)
				$adapter = new tao_helpers_data_GenerisAdapterRdf();
				if($adapter->import($this->options['input'], null, $targetNamespace)){
					$this->outVerbose("RDF 'input' file successfuly imported.");
					$userService->logout();
				}		
				else{
					$userService->logout();
					self::err("An error occured during RDF-XML import.", true);
				}	
			}
        }
        else{
        	self::err("Unable to connect to TAO as '" . $this->options['user'] . "'.", true);
        }
        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037D0 end
    }

    /**
     * Short description of method postRun
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function postRun()
    {
        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037D2 begin
        // section 10-13-1-85-386ea1c0:135859fbbdd:-8000:00000000000037D2 end
    }

} /* end of class tao_scripts_TaoRDFImport */

?>