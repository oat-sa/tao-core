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
			'min'	=> '5.2.6'
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
		45 => array(
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
             'title' => 'PHP Dom extension check',
             'name'  => 'dom'
        ),
		8 => array(
			'type'	=> 'MULTI',
			'title'	=> 'PHP mysql driver extension check',
			'tests'	=> array(
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysql'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysqli'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'pdo_mysql')
			)
		),
		9 => array(
			'type'			=> 'PHP_EXTENSION',
			'title'			=> 'Suhosin patch check',
			'displayMsg'	=> true,
			'name'			=> 'suhosin'
		),
		10 => array(
			'type'	=> 'APACHE_MOD',
			'title'	=> 'Apache mod rewrite check',
			'name'	=> 'rewrite'
		),
		11 => array(
			'type'	=> 'WRITABLE_DIRECTORIES',
			'title'	=> 'System Rights',
			'directories'	=> array(
				'./'			=> array (
					'version'
				),
				'generis'		=> array (
					'generis/data/cache'
					, 'generis/common'
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
		 			, 'taoItems/models/ext/itemAuthoring/waterphenix/xt/xhtml/data/units/'
		 			, 'taoItems/models/ext/itemAuthoring/waterphenix/config'
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
				foreach($test['tests'] as $subTest){
					$parameters = $subTest;
					unset($parameters['type']);
					try{
						$tester = new tao_install_utils_ConfigTester($subTest['type'], $parameters);
						if($tester->getStatus() ==  tao_install_utils_ConfigTester::STATUS_VALID){
							$result['valid'] = true;
						}
						else{
							if($result['message'] != $tester->getMessage()){
								$result['message'] .= $tester->getMessage();
							}
						}
					}
					catch(tao_install_utils_Exception $ie){
						$result['unkown'] = true;
					}
				}
				if($result['valid']){
					$result['message'] = '';
					$result['unkown'] = false;
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
		/*
		 *  1 - Test DB connection (done by the constructor)
		 */ 
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
		} 
		// Else create it
		else {
			try {
				$dbCreator->execute ('CREATE DATABASE "'.$installData['db_name'].'"');
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
		
		
		/*
		 *  3 - Create the local namespace
		 */
		$dbCreator->execute("INSERT INTO models VALUES ('8', '{$installData['module_namespace']}', '{$installData['module_namespace']}#')");
		
		
		/*
		 *  4 - Create the generis config file
		 */
		$generisConfigWriter = new tao_install_utils_ConfigWriter(
			$this->options['root_path'].'generis/common/config.php.in',
			$this->options['root_path'].'generis/common/config.php'
		);
		$generisConfigWriter->createConfig();
		$generisConfigWriter->writeConstants(array(
			'DATABASE_LOGIN'	=> $installData['db_user'],
			'DATABASE_PASS' 	=> $installData['db_pass'],
			'DATABASE_URL'	 	=> $installData['db_host'],
			'SGBD_DRIVER' 		=> $installData['db_driver'],
			'DATABASE_NAME' 	=> $installData['db_name'],
			'LOCAL_NAMESPACE'	=> $installData['module_namespace'],
			'ROOT_PATH'			=> $this->options['root_path'],
			'ROOT_URL'			=> preg_replace("/\/$/", '', $installData['module_url']),
			'DEFAULT_LANG'		=> $installData['module_lang'],
			'DEBUG_MODE'		=> ($installData['module_mode'] == 'debug') ? true : false
		));
		//now we can run the extensions bootstrap
		require_once $this->options['root_path'] . 'generis/common/inc.extension.php';
		// Usefull to get version number from TAO constants
		require_once(ROOT_PATH.'tao/includes/constants.php');
		
		/*
		 *  5 - Create the configuration files for the loaded extensions
		 */
		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extensionManager->getInstalledExtensions();
		foreach($extensions as $extensionId => $extension){
			if($extensionId == 'generis') {
				continue; 	//generis is the root and has been installed above 
			}
			
			$myConfigWriter = new tao_install_utils_ConfigWriter(
				$this->options['root_path'] . $extensionId . '/includes/config.php.sample',
				$this->options['root_path'] . $extensionId . '/includes/config.php'
			);
			$myConfigWriter->createConfig();
		}
		
		$myConfigWriter = new tao_install_utils_ConfigWriter(
			$this->options['root_path'] . '/filemanager/includes/config.php.sample',
			$this->options['root_path'] . '/filemanager/includes/config.php'
		);
		$myConfigWriter->createConfig();
		
		
		$modelCreator = new tao_install_utils_ModelCreator($installData['module_namespace']);

		
		/*
		 *  6 - Insert the extensions models
		 */
		$models = tao_install_utils_ModelCreator::getModelsFromExtensions($extensions);
		foreach($models as $ns => $modelFile){
			$modelCreator->insertModelFile($ns, $modelFile);
		}
		
		
		/*
		 *  7 - Insert Local Data by extensions (can be samples data too)
		 */
		foreach($extensions as $extensionId => $extension){
			if($extensionId == 'generis') {
				continue; 	//generis is the root and has been installed above 
			}
			$localData = $this->options['root_path'] . $extensionId . '/models/ontology/local.rdf';
			if(file_exists($localData)){
				$modelCreator->insertLocalModelFile($localData);
			}
		}
		
		
		/*
		 *  8 - Insert Super User
		 */
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
		 *  9 - Secure the install for production mode
		 */
		if($installData['module_mode'] == 'production'){
			
			// 9.1 Remove Generis User
			$dbCreator->execute('DELETE FROM "statements" WHERE "subject" = \'http://www.tao.lu/Ontologies/TAO.rdf#installator\' AND "modelID"=6');
			
			// 9.2 Protect TAO dist
 			$shield = new tao_install_utils_Shield(array_keys($extensions));
 			$shield->disableRewritePattern(array("!/test/", "!/doc/"));
 			$shield->protectInstall();
		}
		
		/*
		 *  10 - Create the version file
		 */
		file_put_contents(ROOT_PATH.'version', TAO_VERSION);
	}
	
	/**
	 * Run the TAO install from the given data
	 * @throws tao_install_utils_Exception 
	 * @param array $installData
	 */
	public function configWaterPhoenix(array $installData)
	{	
		$waterPhoenixPath = 'taoItems/models/ext/itemAuthoring/waterphenix/';
		$url = trim($installData['module_url']);
		if(!preg_match("/\/$/", $url)){
			$url .= '/';
		}
		
		//Update the HAWAI XHTL skeleton sample
		$contentSkeletonUrlSample = $this->options['root_path'].$waterPhoenixPath.'xt/xhtml/data/units/xhtml.skeleton.xhtml.sample';
		$contentSkeletonUrl = $this->options['root_path'].$waterPhoenixPath.'xt/xhtml/data/units/xhtml.skeleton.xhtml';
		$contentSkeleton = file_get_contents($contentSkeletonUrlSample);
		if (file_exists($contentSkeletonUrlSample)){
			if(!empty($contentSkeleton)){
				$contentSkeleton = str_replace('{WX_URL}', $url.$waterPhoenixPath, $contentSkeleton);
				$contentSkeleton = str_replace('{ROOT_URL}', $url, $contentSkeleton);
				file_put_contents($contentSkeletonUrl, $contentSkeleton);
			}
		}
		
		//update the HAWAI config file
		$configWriter = new tao_install_utils_ConfigWriter(
			$this->options['root_path'] . $waterPhoenixPath . 'config/config.sample',
			$this->options['root_path'] . $waterPhoenixPath . 'config/config.js'
		);
		$configWriter->createConfig();
		$configWriter->writeJsVariable(array(
			'Wx.Config.URL'	=> $url . $waterPhoenixPath
		), "");
		
		//update the HAWAI sample
		$sample = $this->options['root_path'] . 'taoItems/data/i1261571812010328500/index.xhtml';
		if(file_exists($sample)){
			$sampleContent = file_get_contents($sample);
			$sampleContent = str_replace('{ROOT_URL}', $url, $sampleContent);
			file_put_contents($sample, $sampleContent);
		}
	}
}
?>
