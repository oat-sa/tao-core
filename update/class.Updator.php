<?php

if (!defined('UPDATE_URL')){
	define ('UPDATE_URL', 'http://demo.tao.lu/updates/');
}

define ('UPDATE_BASH_PATH', ROOT_PATH.'/tao/update/bash/');
define ('WINDOWS_PATCH_URL', 'http://downloads.sourceforge.net/project/gnuwin32/patch/2.5.9-7/patch-2.5.9-7-bin.zip?r=http%3A%2F%2Fgnuwin32.sourceforge.net%2Fpackages%2Fpatch.htm&ts=1314694863&use_mirror=dfn');
define ('WINDOWS_PATCH_CMD_PATH', UPDATE_BASH_PATH.'patch.exe');
define ('UNIX_PATCH_CMD_PATH', 'patch');
define ('PATCHES_PATH', ROOT_PATH.'/tao/update/patches/');


class tao_update_Updator{
	
	/**
	 * Output of the update function
	 * @var array
	 */
	protected $output = array ();

	/**
	 * Update static version number
	 * @throws tao_update_utils_Exception
	 */
	private function updateVersionNumber($version)
	{
		if (!file_put_contents(ROOT_PATH.'version', $version)){
			$message = __("Unable to write the verson file located at ").ROOT_PATH.__('. Be sure the file is writable.');
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
	}
	
	/**
	 * Get version number of the installed TAO
	 * return {string}
	 */
	public function getCurrentVersion()
	{
		$versionFileContent = @file_get_contents(ROOT_PATH.'version');
		if (empty($versionFileContent)){
			$this->updateVersionNumber(TAO_VERSION);
			$returnValue = TAO_VERSION;
		}else{
			$returnValue = $versionFileContent;
		}
		return $returnValue;
	}

	/**
	 * Get available versions of TAO
	 * @return {array}
	 */
	public function getVersions()
	{
		$returnValue = array ();
		
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
	
		return $returnValue;
	}
	
	/**
	 * Get id of the current TAO revision
	 * @return {int}
	 */
	private function getIdCrtVersion()
	{
		$returnValue = null;
		
		$versions = $this->getVersions();
		$countVersions = count($versions);
		$crtVersion = $this->getCurrentVersion();
		for ($i=0;$i<$countVersions; $i++){
			if ($versions[$i]['version'] == $crtVersion){
				$returnValue = $i;
				break;
			}
		}
		
		return $returnValue;
	}
	
	/**
	 * Get updates' details
	 * @return {array}
	 */
	public function getUpdatesDetails()
	{
		$returnValue = false;
		
		$versions = $this->getVersions();
		$idCrtVersions = $this->getIdCrtVersion();
		$returnValue = array_slice($versions, $idCrtVersions+1);
		
		return $returnValue;
	}
	
	
	/**
	 * Check if the current version of TAO is updatable
	 * @return {boolean}
	 */
	public function checkUpdate()
	{
		$returnValue = false;
		
		$versions = $this->getVersions();
		$idCrtVersion = $this->getIdCrtVersion();
		if ($idCrtVersion<count($versions)-1){
			$returnValue = true;
		}
		
		return $returnValue;
	}
	
	/**
	 * Reverse patch
	 * @param {string} version 
	 * @throws tao_update_utils_Exception
	 */
	private function unpatch($version)
	{
		$patchCommand = $this->getPatchCommand();
		$patchPath = PATCHES_PATH.'TAO_'.$version.'_build.patch';
		$this->output[] = $patchCommand.' -R -d /../../ -p1 < '.$patchPath;
	    exec($patchCommand.' -R -d '.ROOT_PATH.' -p1 < '.$patchPath, $this->output);
	}
	
	
	/**
	 * Patch Tao to the given version
	 * @param {string} version
	 * @throws tao_update_utils_Exception
	 */
	private function patch($version)
	{
		$patchCommand = $this->getPatchCommand();
		$patchContent = @file_get_contents(UPDATE_URL.'TAO_'.$version.'_build.patch');
		$patchOutput = array();
			
		if ($patchContent !== false){
			
			// Copy locally the patch
		    $this->output[] = __('patch found for the version : ').$version;
			$patchPath = PATCHES_PATH.'TAO_'.$version.'_build.patch';
			file_put_contents($patchPath, $patchContent);
			
			// Patch TAO
			$this->output[] = $patchCommand.' -d /../../ -p1 < '.$patchPath;
		    exec($patchCommand.' -d '.ROOT_PATH.' -p1 < '.$patchPath, $patchOutput);
		    
		}else{
			
			$message = __('patch not found for the version : ').$version;
		    $this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		return $patchOutput;
	}
	
	/**
	 * Download windows patch commande
	 * @throws tao_update_utils_Exception
	 */
	private function downloadWinPatchCommand()
	{
		// Download it
		$winPatchUrl = '';				
		$zipFile = ROOT_PATH.'tao/update/bash/windows_patch.zip';
		$dest = @fopen($zipFile, 'w');
		if (!$dest){
			
			$message = __('Unable to create the file ').$zipFile.__('. Be sure that the http user is allowed to write in ').ROOT_PATH.'/tao/update/bash/';
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		$src = @fopen(WINDOWS_PATCH_URL, 'r');
		if (!$src){
			
			$message = __('Unable to get the file ').$winPatchUrl;
			$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		while ($content=fread($src, 1024)){
			fwrite($dest, $content);
		}
		fclose($src);
		fclose($dest);
		
		// Unzip it
		$zip = new ZipArchive();
		if ($zip->open($zipFile) === TRUE) {
		    $zip->extractTo(UPDATE_BASH_PATH, 'bin/patch.exe');
		    $zip->close();
		} else {
			$message = __('unable to unzip patch archive for windows');
    		$this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}
		
		// Get the patch command, drop the rest
		rename(UPDATE_BASH_PATH.'bin/patch.exe', WINDOWS_PATCH_CMD_PATH);
		rmdir(UPDATE_BASH_PATH.'bin/');
		unlink($zipFile);
	}
	
	/**
	 * Check if the patch command exists
	 * @throws tao_update_utils_Exception
	 * @return {boolean}
	 */
	private function getPatchCommand()
	{
		$returnValue = null;
		
		// IF SAFE MODE Throw an exception
		$safe_mode = ini_get('safe_mode');
		if (!empty($safe_mode) && $safe_mode=='1'){
			
			$message = __('Unable to update TAO if php safe mode is enabled');
		    $this->output[] = $message;
			throw new tao_update_utils_Exception($message);
		}else{
			
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
		
		return $returnValue;
	}
	
	/**
	 * Analyse patch output
	 * @param {string} patchOutput
	 * @throw tao_update_utils_Exception
	 */
	private function analysePatchOutput($data)
	{
		// analyse patch output
	    foreach($data as $line){
	    	// Check if some files have been skipped
	    	if (preg_match('/Skipping patch\./i', $line)){
				throw new tao_update_utils_Exception('Some file have been skipped during the update process. The process update has been aborded.');
	    	}
	    }
	}
	
	/**
	 * Run the TAO update from the given data
	 * @throws tao_update_utils_Exception 							// TODO
	 * @param $updateData data coming from the update script		// TODO
	 * @see tao_update_form_Settings								// TODO
	 */
	public function update($version=null)
	{
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
		try {
			$patchOutput = $this->patch($version);
			$this->output = array_merge($this->output, $patchOutput);
			$this->analysePatchOutput($patchOutput);
		}
		catch (tao_update_utils_Exception $e){
			$this->unpatch($version);
			throw $e;
		}
		
		/*
		 * 2 - Update generis config file with the new one
		 */
		
		$generisConfigWriter = new tao_install_utils_ConfigWriter(
			ROOT_PATH.'generis/common/config.php.in',
			ROOT_PATH.'generis/common/config.php'
		);
		$generisConfigWriter->createConfig();
		$generisConfigWriter->writeConstants(array(
			'DATABASE_LOGIN'	=> DATABASE_LOGIN,
			'DATABASE_PASS' 	=> DATABASE_PASS,
			'DATABASE_URL'	 	=> DATABASE_URL,
			'SGBD_DRIVER' 		=> SGBD_DRIVER,
			'DATABASE_NAME' 	=> DATABASE_NAME,
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
		
	}

	/**
	 * Get output
	 * @return {string} 
	 */
	public function getOutput ()
	{
		return $this->output;
	}
}

?>
