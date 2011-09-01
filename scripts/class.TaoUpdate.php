<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.TaoUpdate.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 01.09.2011, 17:10:40 with ArgoUML PHP module 
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
// section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE2-includes begin
// section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE2-includes end

/* user defined constants */
// section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE2-constants begin
// section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE2-constants end

/**
 * Short description of class tao_scripts_TaoUpdate
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoUpdate
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
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE3 begin
    	$this->options = array (
    		'verbose'	=> 'false',
    		'version'	=> null
    	);
    	$this->options = array_merge($this->options, $this->parameters);
    	
    	if($this->options['verbose'] == 'true'){
    		$this->verbose = true;
    	}else{
    		$this->verbose = false;
    	}
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE3 end
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
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE5 begin
		
		//instantiate the updator
		$updator = new tao_update_Updator();
		//get updates details
		$availableUpdates = $updator->getUpdatesDetails();
		//ouput of the update
		$updateOutput = array();
		
		//if target version to update to
		if(!is_null($this->options['version'])){
			$found = false;
			//check if the given version exists
			foreach($availableUpdates as $key=>$update){
				if($found){
					unset($availableUpdates[$key]);
				}
				//if the given version found
				else if ($this->options['version'] == $update['version']){
					$found = true;
				}
			}
		}
		
		if (count($availableUpdates)){
			try{	//if there is any issue during the update, a tao_update_utils_Exception is thrown
				
				foreach ($availableUpdates as $update){
					$updator->update($update['version']);
					$updateOutput = array_merge($updateOutput, $updator->getOutput());
				}
				$updated = true;
				session_destroy();
			}
			catch(tao_update_utils_Exception $ie){
			
				//we display the exception message to the user
				$error = $ie->getMessage();
				self::out($error, array('color' => 'light_red'));
				$updateOutput = array_merge($updateOutput, $updator->getOutput());
			}
			
			//if verbose
			if($this->verbose){
				$outs = $updator->getOutput();
				$count = count($outs);
				for ($i=0; $i<$count; $i++){
					self::out($outs[$i]);
				}
			}
		}
		else {
			//if verbose
			if($this->verbose){
				self::out('No available update(s) found');
			}
		}
    	
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE5 end
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
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE7 begin
        // section 127-0-1-1--3d7d4e14:132257ab24b:-8000:0000000000002EE7 end
    }

} /* end of class tao_scripts_TaoUpdate */

?>