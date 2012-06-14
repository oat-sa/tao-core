<?php

class tao_install_Installator{

	/**
	 * What/How to test the config
	 * @var array tests parameters
	 */
	static $tests = array(
		0 => array(
			'type'	=> 'PHP_VERSION',
			'title'	=> 'PHP version check',
			'displayMsg'	=> true,
			'min'	=> '5.3.0'
		),
		1 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP cUrl extension check',
			'name'	=> 'curl'
		),
		2 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP Zip extension check',
			'name'	=> 'zip'
		),
		3 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP Tidy extension check',
			'name'	=> 'tidy'
		),
		4 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP GD extension check',
			'name'	=> 'gd'
		),
		6 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP Json extension check',
			'name'	=> 'json'
		),
		7 => array(
             'type'  => 'PHP_EXTENSION',
             'title' => 'PHP SPL extension check',
             'name'  => 'spl'
        ),
        8 => array(
             'type'  => 'PHP_EXTENSION',
             'title' => 'PHP Dom extension check',
             'name'  => 'dom'
        ),
        9 => array(
             'type'  => 'PHP_EXTENSION',
             'title' => 'PHP Multibyte String extension check',
             'name'  => 'mbstring'
        ),
		10 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP SVN extension check',
			'name'	=> 'svn'
		),
		11 => array(
			'type'	=> 'MULTI',
			'title'	=> 'PHP db driver extension check',
			'tests'	=> array(
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysql'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysqli'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'pdo_mysql'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'pgsql'),
			),
			'displayMsg'	=> true
		),
		12 => array(
			'type'			=> 'PHP_EXTENSION',
			'title'			=> 'Suhosin patch check',
			'displayMsg'	=> true,
			'name'			=> 'suhosin'
		),
		13 => array(
			'type'	=> 'APACHE_MOD',
			'title'	=> 'Apache mod rewrite check',
			'name'	=> 'rewrite'
		),
		14 => array(
			'type'	=> 'WRITABLE_DIRECTORIES',
			'title'	=> 'System Rights',
			'displayMsg' => true,
			'directories'	=> array(
				'./'			=> array (
					'version'
				),
				'generis'		=> array (
					'generis/data/cache'
                    , 'generis/data/versioning'
					, 'generis/common'
					, 'generis/common/conf'
				)
		 		, 'filemanager' => array (
		 			'filemanager/views/data'
		 			, 'filemanager/includes'
		 		)
		 		, 'tao'			=> array (
		 			'tao/views/export'
		 			, 'tao/includes'
		 			, 'tao/update/patches'
		 			, 'tao/update/bash'
		 		)
		 		, 'taoItems'	=> array (
		 			'taoItems/data'
		 			, 'taoItems/views/export'
		 			, 'taoItems/includes'
		 		)
		 		, 'taoDelivery'	=> array (
		 			'taoDelivery/compiled'
		 			, 'taoDelivery/views/export'
		 			, 'taoDelivery/includes'
		 		)
		 		, 'taoGroups'	=> array (
		 			'taoGroups/views/export'
		 			, 'taoGroups/includes'
		 		)
		 		, 'taoSubjects'	=> array (
		 			'taoSubjects/views/export'
		 			, 'taoSubjects/includes'
		 		)
		 		, 'taoTests'	=> array (
		 			'taoTests/views/export'
		 			, 'taoTests/includes'
		 		)
		 		, 'taoResults'	=> array (
		 			'taoResults/views/export'
		 			, 'taoResults/includes'
		 		)
		 		, 'wfEngine' 	=> array (
		 			'wfEngine/includes'
		 		)
			)
		)
	);

	protected $options = array();

	public function __construct($options)
	{
		if(!isset($options['root_path'])){
			throw new tao_install_utils_Exception("root path options must be defined");
		}
		if(!isset($options['install_path'])){
			throw new tao_install_utils_Exception("install path options must be defined");
		}

		$this->options = $options;

		if(!preg_match("/\/$/", $this->options['root_path'])){
			$this->options['root_path'] .= '/';
		}
		if(!preg_match("/\/$/", $this->options['install_path'])){
			$this->options['install_path'] .= '/';
		}
	}


	/**
	 * Run the tests defined in self::$tests
	 */
	public function processTests()
	{

		$testResults = array();

		foreach(self::$tests as $test){

			//structure of teh results for each test
			$result = array(
				'title' 	=> $test['title'],
				'valid' 	=> false,
				'unknow'	=> false,
				'message'	=> ''
			);

			//in case one of the test is sufficient: only one of the tests must be valid
			if($test['type'] == 'MULTI'){
				$successMessages = array();
				$failureMessages = array();
				foreach($test['tests'] as $subTest){
					$parameters = $subTest;
					unset($parameters['type']);
					try{
						$tester = new tao_install_utils_ConfigTester($subTest['type'], $parameters);
						if($tester->getStatus() ==  tao_install_utils_ConfigTester::STATUS_VALID){
							$result['valid'] = true;
							$successMessages[] = $tester->getMessage();
						} else {
							$failureMessages[] = $tester->getMessage();
						}
					}
					catch(tao_install_utils_Exception $ie){
						$result['unkown'] = true;
					}
				}
				$result['message'] = implode('<br />', array_unique($successMessages));
				if($result['valid']){
					if (isset($test['displayMsg']) && $test['displayMsg'] === true) {
						$result['message'] = implode('<br />', array_unique($successMessages));
					} else {
						$result['message'] = '';
					}
					$result['unkown'] = false;
				} else {
					$result['message'] = implode('<br />', array_unique($failureMessages));
				}
			}
			else{
				//the test must be valid

				$parameters = $test;
				unset($parameters['type']);
				unset($parameters['title']);
				unset($parameters['displayMsg']);
				try{
					$tester = new tao_install_utils_ConfigTester($test['type'], $parameters);
					switch($tester->getStatus()){
						case tao_install_utils_ConfigTester::STATUS_VALID:
							$result['valid'] = true;
							if(isset($test['displayMsg'])){
								$result['message'] = $tester->getMessage();
							}
							break;
						case tao_install_utils_ConfigTester::STATUS_INVALID:
							$result['message'] = $tester->getMessage();
							break;
						case tao_install_utils_ConfigTester::STATUS_UNKNOW:
							$result['unkown'] = true;
							$result['message'] = $tester->getMessage();
							break;
					}
				}
				catch(tao_install_utils_Exception $ie){
					$result['unkown'] = true;
					$result['message'] = "An error occurs during the test: $ie";
					common_Logger::e("An error occurs during the test: $ie", 'INSTALL');
				}
			}
			$testResults[] = $result;
		}
		return $testResults;
	}


	/**
	 * Run the TAO install from the given data
	 * @throws tao_install_utils_Exception
	 * @param $installData data coming from the install form
	 * @see tao_install_form_Settings
	 */
	public function install(array $installData)
	{
	    set_time_limit(300);
		common_Logger::i('Starting TAO install', 'INSTALL');
        
		/*
		 *  1 - Test DB connection (done by the constructor)
		 */
		common_Logger::i("Spawning DbCreator", 'INSTALL');
		$dbCreator = new tao_install_utils_DbCreator(
			$installData['db_host'],
			$installData['db_user'],
			$installData['db_pass'],
			$installData['db_driver'],
			$installData['db_name']
		);

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
				$dbCreator->execute ('CREATE DATABASE "'.$installData['db_name'].'"');
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
		
		// Insert stored procedures for the selected driver if they are found.
		if(stripos($installData['db_driver'], 'postgres') !== false) {
		    // postgres driver can be postgres, postgres7, postgres8, ...
		    $procDbDriver = 'postgres';    
		}else{
		    $procDbDriver = $installData['db_driver'];
		}
        
		$storedProcedureFile = $this->options['install_path'].'db/tao_stored_procedures_'.$procDbDriver.'.sql';
		if (file_exists($storedProcedureFile) && is_readable($storedProcedureFile)){
			common_Logger::i('Installing stored procedures for '.$procDbDriver, 'INSTALL');
			$sqlParserClassName = 'tao_install_utils_' . ucfirst($procDbDriver) . 'ProceduresParser';
			$dbCreator->setSQLParser(new $sqlParserClassName());
			$dbCreator->load($storedProcedureFile);
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
			'LOCAL_NAMESPACE'	=> $installData['module_namespace'],
			'ROOT_PATH'			=> $this->options['root_path'],
			'ROOT_URL'			=> preg_replace("/\/$/", '', $installData['module_url']),
			'DEFAULT_LANG'		=> $installData['module_lang'],
			'DEBUG_MODE'		=> ($installData['module_mode'] == 'debug') ? true : false
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
		$toInstall = common_ext_ExtensionsManager::singleton()->getAvailableExtensions();
		foreach ($toInstall as $extension) {
			try {
			    $importLocalData = ($installData['import_local'] == array('on'));
				$extinstaller = new common_ext_ExtensionInstaller($extension, $importLocalData);
				$extinstaller->install();
			} catch (common_ext_ExtensionException $e) {
				common_Logger::w('Exception('.$e->getMessage().') during install for extension "'.$extension->getID().'"');
			}
		}
		
        /*
         * 9 - Flush File Cache
         */
        common_Logger::i("Purging filecache ", 'INSTALL');
		tao_models_classes_cache_FileCache::singleton()->purge();

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
		
		common_Logger::d('Precalculkating funcACL role accesses', 'INSTALL');
		tao_helpers_funcACL_funcACL::buildRolesByActions();
		
		common_Logger::i('Instalation completed', 'INSTALL');
        
        /*
         * 13 - Miscellaneous
         */
        // Localize item content for demo items.
        $dbCreator->execute("UPDATE statements SET l_language = '" . $installData['module_lang'] . "' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent'");
	}
}
?>
