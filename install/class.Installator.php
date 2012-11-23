<?php

class tao_install_Installator{

	static $defaultExtensions = array('tao' ,'filemanager','taoItems','wfEngine','taoResults','taoTests','taoDelivery','taoGroups','taoSubjects', 'wfAuthoring');
	
	protected $options = array();
	
	private $toInstall = array();

	public function __construct($options)
	{
		if(!isset($options['root_path'])){
			throw new tao_install_utils_Exception("root_path option must be defined to perform installation.");
		}
		if(!isset($options['install_path'])){
			throw new tao_install_utils_Exception("install_path option must be defined to perform installation.");
		}

		$this->options = $options;

		if(substr($this->options['root_path'], -1) != DIRECTORY_SEPARATOR){
			$this->options['root_path'] .= DIRECTORY_SEPARATOR;
		}
		if(substr($this->options['install_path'], -1) != DIRECTORY_SEPARATOR){
			$this->options['install_path'] .= DIRECTORY_SEPARATOR;
		}
		
		if(isset($options['extensions']) && is_array($options['extensions'])) {
			$this->toInstall = $options['extensions']; 
		} else {
			$this->toInstall = self::$defaultExtensions;
		}
	}


	/**
	 * Run the TAO install from the given data
	 * @throws tao_install_utils_Exception
	 * @param $installData data coming from the install form
	 * @see tao_install_form_Settings
	 */
	public function install(array $installData)
	{
		try
		{
			set_time_limit(300);
			common_Logger::i('Starting TAO install', 'INSTALL');
	        
			// Sanitize $installData if needed.
			if(!preg_match("/\/$/", $installData['module_url'])){
				$installData['module_url'] .= '/';
			}
			
			/*
			 *  0 - Check configuration.
			 */
			$distribManifest = new common_distrib_Manifest(dirname(__FILE__) . '/../distributions.php');
			$distrib = $distribManifest->getDistributions();
			$distrib = $distrib[1]; // At the moment, we use the Open Source Distribution by default.
			$configChecker = $distrib->getConfigChecker();
			$reports = $configChecker->check();
			foreach ($reports as $r){
				$msg = $r->getMessage();
				$component = $r->getComponent();
				common_Logger::i($msg);

				if ($r->getStatus() !== common_configuration_Report::VALID && !$component->isOptional()){
					throw new tao_install_utils_Exception($msg);
				}
			}
			
			/*
			 *  1 - Test DB connection (done by the constructor)
			 */
			$installData['db_driver'] = strtolower(str_replace('pdo_', '', $installData['db_driver']));
			
			common_Logger::i("Spawning DbCreator", 'INSTALL');
			$dbCreatorClassName = tao_install_utils_DbCreator::getClassNameForDriver($installData['db_driver']);
			$dbCreator = new $dbCreatorClassName(
				$installData['db_host'],
				$installData['db_user'],
				$installData['db_pass'],
				$installData['db_driver'],
				$installData['db_name']
			);
			common_Logger::d("DbCreator spawned", 'INSTALL');
	
			/*
			 *   2 - Load the database schema
			 */
	
			// If the database already exists, drop all tables
			if ($dbCreator->dbExists($installData['db_name'])) {
				$dbCreator->cleanDb ($installData['db_name']);
				common_Logger::i("Droped all tables", 'INSTALL');
			}
			// Else create it
			else {
				try {
					$dbCreator->createDatabase($installData['db_name']);
					common_Logger::i("Created database ".$installData['db_name'], 'INSTALL');
				} catch (Exception $e){
					throw new tao_install_utils_Exception('Unable to create the database, make sure that '.$installData['db_user'].' is granted to create databases. Otherwise create the database with your super user and give to  '.$installData['db_user'].' the right to use it.');
				}
				// If the target Sgbd is mysql select the database after creating it
				if ($installData['db_driver'] == 'mysql'){
					$dbCreator->setDatabase ($installData['db_name']);
				}
			}
	
			// Create tao tables
			$dbCreator->load($this->options['install_path'].'db/tao.sql', array('DATABASE_NAME' => $installData['db_name']));
			common_Logger::i('Created tables', 'INSTALL');
	        
			$storedProcedureFile = $this->options['install_path'].'db/tao_stored_procedures_' . $installData['db_driver'] . '.sql';
			if (file_exists($storedProcedureFile) && is_readable($storedProcedureFile)){
				common_Logger::i('Installing stored procedures for ' . $installData['db_driver'], 'INSTALL');
				$dbCreator->loadProc($storedProcedureFile);
			}
			
			/*
			 *  3 - Create the local namespace
			 */
			common_Logger::i('Creating local namespace', 'INSTALL');
			$dbCreator->execute("INSERT INTO models VALUES ('8', '{$installData['module_namespace']}', '{$installData['module_namespace']}#')");
	
			/*
			 *  4 - Create the generis config files
			 */
			
			common_Logger::d('Writing db config', 'INSTALL');
			$dbConfigWriter = new tao_install_utils_ConfigWriter(
					$this->options['root_path'].'generis/common/conf/sample/db.conf.php',
					$this->options['root_path'].'generis/common/conf/db.conf.php'
			);
			$dbConfigWriter->createConfig();
			$dbConfigWriter->writeConstants(array(
				'DATABASE_LOGIN'	=> $installData['db_user'],
				'DATABASE_PASS' 	=> $installData['db_pass'],
				'DATABASE_URL'	 	=> $installData['db_host'],
				'SGBD_DRIVER' 		=> $installData['db_driver'],
				'DATABASE_NAME' 	=> $installData['db_name']
			));
			
			common_Logger::d('Writing generis config', 'INSTALL');
			$generisConfigWriter = new tao_install_utils_ConfigWriter(
				$this->options['root_path'].'generis/common/conf/sample/generis.conf.php',
				$this->options['root_path'].'generis/common/conf/generis.conf.php'
			);
			
			$generisConfigWriter->createConfig();
			$generisConfigWriter->writeConstants(array(
				'LOCAL_NAMESPACE'			=> $installData['module_namespace'],
				'GENERIS_INSTANCE_NAME'		=> $installData['instance_name'],
				'GENERIS_SESSION_NAME'		=> self::generateSessionName(),
				'ROOT_PATH'					=> $this->options['root_path'],
				'ROOT_URL'					=> $installData['module_url'],
				'DEFAULT_LANG'				=> $installData['module_lang'],
				'DEBUG_MODE'				=> ($installData['module_mode'] == 'debug') ? true : false
			));
			
			/*
			 * 5 - Run the extensions bootstrap
			 */
			common_Logger::d('Running the extensions bootstrap', 'INSTALL');
			require_once $this->options['root_path'] . 'generis/common/inc.extension.php';
			
			// Usefull to get version number from TAO constants
			common_Logger::d('Including tao constants', 'INSTALL');
			require_once(ROOT_PATH.'tao/includes/constants.php');
	
			/*
			 * 6 - Adding languages
			 */
			
			$modelCreator = new tao_install_utils_ModelCreator(LOCAL_NAMESPACE);
			$models = $modelCreator->getLanguageModels();
	        foreach ($models as $ns => $modelFiles){
	            foreach ($modelFiles as $file){
	                common_Logger::d("Inserting language description model '".$file."'", 'INSTALL');
	            	$modelCreator->insertLocalModel($file);
	            }
	        }

			/*
			 * 7 - Finish Generis Install
			 */
			$generis = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');
			$generisInstaller = new common_ext_GenerisInstaller($generis);
			$generisInstaller->install();
	        
	        /*
			 * 8 - Install the extensions
			 */
			$toInstall = array();
			foreach ($this->toInstall as $id) {
				try {
					$ext = common_ext_ExtensionsManager::singleton()->getExtensionById($id);
					if (!$ext->isInstalled()) {
						$toInstall[] = $ext;
					}
				} catch (common_ext_ExtensionException $e) {
					common_Logger::w('Extension '.$id.' not found');
				}
			}
			while (!empty($toInstall)) {
				$formerCount = count($toInstall);
				foreach ($toInstall as $key => $extension) {
					// if all dependencies are installed
					$installed	= array_keys(common_ext_ExtensionsManager::singleton()->getInstalledExtensions());
					$missing	= array_diff($extension->getDependencies(), $installed);
					if (count($missing) == 0) {
						try {
						    $importLocalData = ($installData['import_local'] == true);
							$extinstaller = new common_ext_ExtensionInstaller($extension, $importLocalData);
							$extinstaller->install();
						} catch (common_ext_ExtensionException $e) {
							throw new tao_install_utils_Exception("An error occured during the installation of extension '" . $extension->getID() . "'.");
							common_Logger::w('Exception('.$e->getMessage().') during install for extension "'.$extension->getID().'"');
						}
						unset($toInstall[$key]);
					}
				}
				if ($formerCount == count($toInstall)) {
					throw new common_exception_Error('Unfulfilable/Cyclic reference found in extensions');
				}
			}
			
	        /*
	         * 9 - File Cache purged in Extension Instaltion
	         */
	
			/*
			 *  10 - Insert Super User
			 */
			common_Logger::i('Spawning SuperUser '.$installData['user_login'], 'INSTALL');
			$modelCreator->insertSuperUser(array(
				'login'			=> $installData['user_login'],
				'password'		=> md5($installData['user_pass1']),
				'userLastName'	=> $installData['user_lastname'],
				'userFirstName'	=> $installData['user_firstname'],
				'userMail'		=> $installData['user_email'],
				'userDefLg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.strtoupper($installData['module_lang']),
				'userUILg'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#Lang'.strtoupper($installData['module_lang'])
			));
	
			/*
			 *  11 - Secure the install for production mode
			 */
			if($installData['module_mode'] == 'production'){
				$extensions = $extensionManager->getInstalledExtensions();
				common_Logger::i('Securing tao for production', 'INSTALL');
				
				// 11.1 Remove Generis User
				$dbCreator->execute('DELETE FROM "statements" WHERE "subject" = \'http://www.tao.lu/Ontologies/TAO.rdf#installator\' AND "modelID"=6');
	
				// 11.2 Protect TAO dist
	 			$shield = new tao_install_utils_Shield(array_keys($extensions));
	 			$shield->disableRewritePattern(array("!/test/", "!/doc/"));
	 			$shield->protectInstall();
			}
	
			/*
			 *  12 - Create the version file
			 */
			common_Logger::d('Creating version file for TAO', 'INSTALL');
			file_put_contents(ROOT_PATH.'version', TAO_VERSION);
			
			common_Logger::d('Computing funcACL role accesses', 'INSTALL');
			tao_helpers_funcACL_funcACL::buildRolesByActions();
			
			common_Logger::i('Instalation completed', 'INSTALL');
	        
	        /*
	         * 13 - Miscellaneous
	         */
	        // Localize item content for demo items.
	        $dbCreator->execute("UPDATE statements SET l_language = '" . $installData['module_lang'] . "' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent'");	
		}
		catch(Exception $e){
			// In any case, we transmit a single exception type (at the moment)
			// for a clearer API for client code.
			throw new tao_install_utils_Exception($e->getMessage(), 0, $e);
		}
	}
	
	/**
     * Generate an alphanum token to be used as a PHP session name.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
	public static function generateSessionName(){
		$name = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $length = 8;
        $maxIndex = strlen($chars) - 1;
        
	    for ($i = 0; $i < $length; $i++) {
	    	$name .= $chars[rand(0, $maxIndex)];
	 	}
	 	
	 	return 'tao_' . $name;
	}
}
?>
