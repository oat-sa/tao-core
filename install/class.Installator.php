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
		4 => array(
			'type'	=> 'PHP_EXTENSION',
			'title'	=> 'PHP GD extension check',
			'name'	=> 'gd'
		),
		5 => array(
			'type'	=> 'MULTI',
			'title'	=> 'PHP mysql driver extension check',
			'tests'	=> array(
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysql'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'mysqli'),
				array('type'	=> 'PHP_EXTENSION', 'name' =>  'pdo_mysql')
			)
		),
		6 => array(
			'type'	=> 'APACHE_MOD',
			'title'	=> 'Apache mod rewrite check',
			'name'	=> 'rewrite'
		)
	);

	/**
	 * Run the tests defined in self::$tests
	 */
	public function processTests(){
		
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
				try{
					$tester = new tao_install_utils_ConfigTester($test['type'], $parameters);
					switch($tester->getStatus()){
						case tao_install_utils_ConfigTester::STATUS_VALID:
							$result['valid'] = true;
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
	
}
?>