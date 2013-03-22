<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - tao/update/utils/class.Patcher.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 06.09.2011, 10:34:39 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update_utils
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055ED-includes begin
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055ED-includes end

/* user defined constants */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055ED-constants begin
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055ED-constants end

/**
 * Short description of class tao_update_utils_Patcher
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update_utils
 */
class tao_update_utils_Patcher
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Errors of the class
     *
     * @access protected
     * @var array
     */
    protected $errors = array();

    /**
     * Outputs of the class
     *
     * @access protected
     * @var array
     */
    protected $output = array();

    /**
     * Path of the patch file to use to patch the source code
     *
     * @access protected
     * @var string
     */
    protected $patch = '';

    /**
     * Options to pass to the patch command
     *
     * @access private
     * @var array
     */
    private $options = array();

    // --- OPERATIONS ---

    /**
     * Apply the patch following options given to the constructor 
     * (patch, target and options)
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string patch
     * @param  string target
     * @param  array options
     * @return boolean
     */
    public function patch($patch, $target, $options)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F1 begin
        
    	$patchCommand = $this->getPatchCommand();
    	// Patch TAO
    	$patchOutput = array();
    	$patchOptions = '';
    	foreach ($this->options as $name=>$value){
    		$patchOptions .= ' -'.$name.' '.$value;
    	}
		$this->output[] = $patchCommand.' -d '.$this->target.' '.$patchOptions.' < '.$this->patch;
    	exec($patchCommand.' -d '.$this->target.' '.$patchOptions.' < '.$this->patch, $patchOutput);
        $this->output = array_merge($this->output, $patchOutput);
    	
    	//analyse patch output
    	$this->analysePatchOutput();
    	$returnValue = true;
    	
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F1 end

        return (bool) $returnValue;
    }

    /**
     * Reverse the last applied patch
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string patch
     * @param  string target
     * @param  array options
     * @return boolean
     */
    public function unpatch($patch, $target, $options)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F3 begin
        
        $patchCommand = $this->getPatchCommand();
        $unpatchOutput = array ();
		$this->output[] = $patchCommand.' -R -d '.$this->target.' '.$this->getSerializedOptions().' < '.$this->patch;
	    exec(patchCommand.' -R -d '.$this->target.' '.$this->getSerializedOptions().' < '.$this->patch, $unpatchOutput);
	    $returnValue = true;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F3 end

        return (bool) $returnValue;
    }

    /**
     * Get the patch command to use to patch the source code functions of the 
     * Operating system.
     * If the detected OS is windows the function will download the windows GNU 
     * patch command from sourceforge server.
     * If you do not want the function downloads the windows patch command put 
     * the patch.exe command in the directory  : tao/update/bash/
     *
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     * @see tao_update_utils_Patcher::downloadWinPatchCommand
     */
    protected function getPatchCommand()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F5 begin
        
    	// IF SAFE MODE Throw an exception
		$safe_mode = ini_get('safe_mode');
		if (!empty($safe_mode) && $safe_mode=='1'){
			
			$message = __('Unable to update TAO if php safe mode is enabled');
		    $this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		else{
			
			if (PHP_OS == 'WINNT'){
				
				// Check if the patch executable is available for the windows platform
				if (!file_exists(WINDOWS_PATCH_CMD_PATH)){
					$this->downloadWinPatchCommand();
					$returnValue = WINDOWS_PATCH_CMD_PATH.' --binary ';
				}else {
					$returnValue = WINDOWS_PATCH_CMD_PATH.' --binary ';
				}
			}else{
				
				$returnValue = 'patch';
			}
		}
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F5 end

        return (string) $returnValue;
    }

    /**
     * download the windows GNU patch command
     *
     * @access protected
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return boolean
     */
    protected function downloadWinPatchCommand()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F7 begin
        
		$zipFile = ROOT_PATH.'tao/update/bash/windows_patch.zip';
		// Open the destination zip file
		$dest = @fopen($zipFile, 'w');
		if (!$dest){
			
			$message = __('Unable to create the file ').$zipFile.__('. Be sure that the http user is allowed to write in ').ROOT_PATH.'/tao/update/bash/';
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		// Downloas the windows patch command
		$src = @fopen(WINDOWS_PATCH_URL, 'r');
		if (!$src){
			
			$message = __('Unable to get the file ').WINDOWS_PATCH_URL;
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		while ($content=fread($src, 1024)){
			fwrite($dest, $content);
		}
		fclose($src);
		fclose($dest);
		
		// Extract the windows patch command
		$zip = new ZipArchive();
		if ($zip->open($zipFile) === TRUE) {
			
		    $zip->extractTo(UPDATE_BASH_PATH, 'bin/patch.exe');
			rename(UPDATE_BASH_PATH.'bin/patch.exe', WINDOWS_PATCH_CMD_PATH); // mv patch command
			rmdir(UPDATE_BASH_PATH.'bin/'); // remove extracted archive
		    $zip->close();
		} 
		else {
			
			$message = __('unable to unzip patch archive for windows, make sure that the php zip extension is enabled');
    		$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		// Get the patch command, drop the rest
		unlink($zipFile);
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055F7 end

        return (bool) $returnValue;
    }

    /**
     * get error
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getError()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005602 begin
        
        $returnValue = $this->error;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005602 end

        return (array) $returnValue;
    }

    /**
     * get output
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getOutput()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005604 begin
        
        $returnValue = $this->output;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005604 end

        return (array) $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string patch
     * @param  string target
     * @param  array options
     * @return mixed
     */
    public function __construct($patch, $target, $options)
    {
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005619 begin
        
        $this->patch = $patch;
        $this->target = $target;
        $this->options = $options;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005619 end
    }

    /**
     * Analyse the trace of the patch command.
     * Throw an exception if an error has been found.
     *
     * @access private
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    private function analysePatchOutput()
    {
        // section 127-0-1-1--27aeaa7:1323dd23d2a:-8000:0000000000002F9B begin
        
        foreach($this->output as $line){
	    	// Check if some files have been skipped
	    	if (preg_match('/Skipping patch\./i', $line)){
				throw new tao_update_utils_Exception('Some file have been skipped during the update process. The process update has been aborded.');
	    	}
        }
        
        // section 127-0-1-1--27aeaa7:1323dd23d2a:-8000:0000000000002F9B end
    }

} /* end of class tao_update_utils_Patcher */

?>