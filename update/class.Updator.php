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
 * TAO - tao/update/class.Updator.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 06.09.2011, 10:30:52 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_update_utils_Patcher
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/update/utils/class.Patcher.php');

/* user defined includes */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0A-includes begin
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0A-includes end

/* user defined constants */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0A-constants begin
if (!defined('UPDATE_URL')){
	define ('UPDATE_URL', 'http://demo.tao.lu/updates/');
}
define ('UPDATE_BASH_PATH', ROOT_PATH.'/tao/update/bash/');
define ('WINDOWS_PATCH_URL', 'http://downloads.sourceforge.net/project/gnuwin32/patch/2.5.9-7/patch-2.5.9-7-bin.zip?r=http%3A%2F%2Fgnuwin32.sourceforge.net%2Fpackages%2Fpatch.htm&ts=1314694863&use_mirror=dfn');
define ('WINDOWS_PATCH_CMD_PATH', UPDATE_BASH_PATH.'patch.exe');
define ('UNIX_PATCH_CMD_PATH', 'patch');
define ('PATCHES_PATH', ROOT_PATH.'/tao/update/patches/');
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0A-constants end

/**
 * Short description of class tao_update_Updator
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update
 */
class tao_update_Updator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute output
     *
     * @access protected
     * @var array
     */
    protected $output = array();

    /**
     * Short description of attribute error
     *
     * @access public
     * @var array
     */
    public $error = array();

    // --- OPERATIONS ---

    /**
     * Update the version number of the TAO install
     *
     * @access private
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string version
     * @return mixed
     */
    private function updateVersionNumber($version)
    {
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0E begin
    	
    	if (!file_put_contents(ROOT_PATH.'version', $version)){
			$message = __("Unable to write the verson file located at ").ROOT_PATH.__('. Be sure the file is writable.');
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
    	
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F0E end
    }

    /**
     * Get the current version identifier
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public function getCurrentVersion()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F11 begin
        
        $versionFileContent = @file_get_contents(ROOT_PATH.'version');
		if (empty($versionFileContent)){
			$this->updateVersionNumber(TAO_VERSION);
			$returnValue = TAO_VERSION;
		}else{
			$returnValue = $versionFileContent;
		}
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F11 end

        return (string) $returnValue;
    }

    /**
     * Get all the versions of TAO
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getVersions()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F13 begin
        
    	// Get releases details from tao update service
		$xml = @simplexml_load_file(UPDATE_URL.'releases.xml');
		if (!$xml){
			$message = __("Unable to reach the update server located at ").UPDATE_URL;
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		$releasesNodes = $xml->children();
		foreach ($releasesNodes as $releaseNode){
			$versionNode = $releaseNode->xpath('version');
			$commentNode = $releaseNode->xpath('comment');
			$returnValue[] = array (
				'version'	=> (string) $versionNode[0]
				, 'comment'	=> (string) trim($commentNode[0])
			);
		}
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F13 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getIdCrtVersion
     *
     * @access private
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return tao_helpers_form_validators_Integer
     */
    private function getIdCrtVersion()
    {
        $returnValue = null;

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F15 begin
        
    	$versions = $this->getVersions();
		$countVersions = count($versions);
		$crtVersion = $this->getCurrentVersion();
		for ($i=0;$i<$countVersions; $i++){
			if ($versions[$i]['version'] == $crtVersion){
				$returnValue = $i;
				break;
			}
		}
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F15 end

        return $returnValue;
    }

    /**
     * Get details about the available updates
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getUpdatesDetails()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F18 begin
        
        $versions = $this->getVersions();
		$idCrtVersions = $this->getIdCrtVersion();
		$returnValue = array_slice($versions, $idCrtVersions+1);
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F18 end

        return (array) $returnValue;
    }

    /**
     * Check if the current version requires updates
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return boolean
     */
    public function checkUpdate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F1A begin
        
    	$versions = $this->getVersions();
		$idCrtVersion = $this->getIdCrtVersion();
		if ($idCrtVersion<count($versions)-1){
			$returnValue = true;
		}
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000002F1A end

        return (bool) $returnValue;
    }

    /**
     * Update TAO
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string version
     * @return mixed
     */
    public function update($version)
    {
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055E8 begin
        
    	$dbCreator = new tao_install_utils_DbCreator(DATABASE_URL, DATABASE_LOGIN, DATABASE_PASS, SGBD_DRIVER);
		$dbCreator->setDatabase(DATABASE_NAME);
		
		if (empty($version)){
			$message = __('first paramater (version number:string) required');
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		/*
		 *  0 - Perform some check before update
		 */
		// patches directory is writable
		if (!is_writable(PATCHES_PATH)){
			$message = __('The directory ').PATCHES_PATH.__(' has to be writable');
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		/*
		 *  1 - Apply a patch on the code if it is existing
		 */
		// Download the patch to apply
		$patchPath = $this->getPatch($version);
		$patcher = new tao_update_utils_Patcher($patchPath, dirname(__FILE__).'/../../', array('p1'=>''));
		
		try {
			$patcher->patch();
			$this->output = array_merge($this->output, $patcher->getOutput());
		}
		catch (tao_update_utils_Exception $e){
			$this->unpatch($version);
			$this->output = array_merge($this->output, $patcher->getOutput());
			$error = $patcher->getError();
			throw $e;
		}
		
		/*
		 * 2 - Update generis config file with the new one
		 */
		
		common_Logger::d('Writing db config', 'INSTALL');
		$dbConfigWriter = new tao_install_utils_ConfigWriter(
				ROOT_PATH.'generis/common/conf/sample/db.conf',
				ROOT_PATH.'generis/common/conf/db.conf.php'
		);
		$dbConfigWriter->createConfig();
		$dbConfigWriter->writeConstants(array(
			'DATABASE_LOGIN'	=> DATABASE_LOGIN,
			'DATABASE_PASS' 	=> DATABASE_PASS,
			'DATABASE_URL'	 	=> DATABASE_URL,
			'SGBD_DRIVER' 		=> SGBD_DRIVER,
			'DATABASE_NAME' 	=> DATABASE_NAME
		));
		
		common_Logger::d('Writing generis config', 'INSTALL');
		$generisConfigWriter = new tao_install_utils_ConfigWriter(
			ROOT_PATH.'generis/common/conf/sample/generis.conf.php',
			ROOT_PATH.'generis/common/conf/generis.conf.php'
		);
		$generisConfigWriter->createConfig();
		$generisConfigWriter->writeConstants(array(
			'LOCAL_NAMESPACE'	=> LOCAL_NAMESPACE,
			'ROOT_PATH'			=> ROOT_PATH,
			'ROOT_URL'			=> ROOT_URL,
			'DEFAULT_LANG'		=> DEFAULT_LANG,
			'DEBUG_MODE'		=> DEBUG_MODE
		));
		
		/*
		 * 3 - Update TAO config files
		 */
		
		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extensionManager->getInstalledExtensions();
		foreach($extensions as $extensionId => $extension){
			if($extensionId == 'generis') {
				continue; 	//generis is the root and has been installed above 
			}
			
			$myConfigWriter = new tao_install_utils_ConfigWriter(
				ROOT_PATH . $extensionId . '/includes/config.php.sample',
				ROOT_PATH . $extensionId . '/includes/config.php'
			);
			$myConfigWriter->createConfig();
		}
		
		$myConfigWriter = new tao_install_utils_ConfigWriter(
			ROOT_PATH . '/filemanager/includes/config.php.sample',
			ROOT_PATH . '/filemanager/includes/config.php'
		);
		$myConfigWriter->createConfig();
		
		/*
		 * 4 - Apply update scripts if they are existing
		 */
		
		//get the files to launch to update TAO
		$pattern = dirname(__FILE__).'/scripts/'.$version.'/';
		if(file_exists($pattern) && is_dir($pattern)){
			
			if(isset ($scriptNumber) && $scriptNumber !== false){
				$pattern .= $scriptNumber;
			}
			$pattern .= '*';
			
			$updateFiles = array();
			foreach(glob($pattern) as $path){
					$updateFiles[basename($path)] = $path;
			}
			//sort them by number
			ksort($updateFiles);
			foreach($updateFiles as $file => $path){
				
				//import rdf files
				if(preg_match("/\.rdf$/", $file)){
					try{
						$modelCreator = new tao_install_utils_ModelCreator(LOCAL_NAMESPACE);
						//extract namespace from the file
						$xml = simplexml_load_file($path);
						$attrs = $xml->attributes('xml', true);
						if(!isset($attrs['base']) || empty($attrs['base'])){
							throw new Exception('The namespace of the rdf file to import has to be defined with the "xml:base" attribute of the ROOT node');
						}
						$ns = (string) $attrs['base'];
						//import the model in the ontology
						$modelCreator->insertModelFile($ns, $path);
					}
					catch(Exception $e){
						$this->output[] = $e->getMessage();
					}
				}
				
				//execute php files
				if(preg_match("/\.php$/", $file)){
					$this->output[] = "running $file";
					include $path;
				}
				
				//execute SQL queries
				if(preg_match("/\.sql$/", $file)){
					$this->output[] = "loading $file";
					try{
						// destroy the wrapper if it has been patched
						//$dbWrapper = core_kernel_classes_DbWrapper::singleton();
						//unset ($dbWrapper);
						//core_kernel_classes_DbWrapper::singleton()->load($path);
						$dbCreator->load($path);
					}catch(Exception $e){
						$this->output[] = $e->getMessage();
					}
				}
			}
		}
		
		/*
		 *  5 - Update version number
		 */
		$this->updateVersionNumber($version);
    	
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055E8 end
    }

    /**
     * Get outputs
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getOutput()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005608 begin
        
        $returnValue = $this->output;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005608 end

        return (array) $returnValue;
    }

    /**
     * Get errors
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return array
     */
    public function getError()
    {
        $returnValue = array();

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:000000000000560D begin
        
        $returnValue = $this->error;
        
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:000000000000560D end

        return (array) $returnValue;
    }

    /**
     * Get the patch file to apply function of a version identifier
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string version
     * @return string
     */
    public function getPatch($version)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:000000000000560F begin
        
		$patchPath = PATCHES_PATH.'TAO_'.$version.'_build.patch';

		// The patch exists in the local patch repository
		if (file_exists($patchPath)){
			
			$returnValue = $patchPath;
		}
		// Else download it
		else {
			
			$patchContent = @file_get_contents(UPDATE_URL.'TAO_'.$version.'_build.patch');
			if ($patchContent !== false){
				
				// Copy locally the patch
			    $this->output[] = __('patch found for the version : ').$version;
				file_put_contents($patchPath, $patchContent);
				$returnValue = $patchPath;
			} 
			else {
				
				$message = __('patch not found for the version : ').$version;
			    $this->output[] = $message;
				throw new tao_update_utils_Exception ($message);
			}
		}
			
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:000000000000560F end

        return (string) $returnValue;
    }

} /* end of class tao_update_Updator */

?>