<?php
define('REL_PATH', '../../');
require_once(REL_PATH . 'tao/helpers/class.File.php');
require_once('class.Exception.php');

/**
 * The ConfigTester tester class enables you to test the server configuration
 * 
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 */
class tao_install_utils_ConfigTester{
	
	/**
	 * @var string 
	 */
	private $message = "";
	
	/**
	 * @var int the test status
	 */
	private $status;
	
	const STATUS_UNKNOW  = 1;
	const STATUS_VALID	 = 2;
	const STATUS_INVALID = 3;
	
	
	private static $_types = array(
		'PHP_VERSION',
		'APACHE_MOD',
		'PHP_EXTENSION',
		'WRITABLE_DIRECTORIES'
	);
	
	/**
	 * Constructor,
	 * do the test regarding the type in parameter
	 * @param string $type one of the _types to test
	 * @param array $options
	 */
	public function __construct($type, $options){
		if(!in_array(strtoupper($type),self::$_types)){
			throw new tao_install_utils_Exception('Unknow config type : '.$type);
		}
		switch($type){
			case 'PHP_VERSION'	: 
				isset($options['max']) ? $max = $options['max'] : $max = null;
				$this->checkPhpVersion($options['min'], $max); 
				break;
			case 'APACHE_MOD'	: $this->checkApacheMod($options['name']); 		break;
			case 'PHP_EXTENSION': $this->checkPhpExtension($options['name']); 	break;
			case 'WRITABLE_DIRECTORIES': $this->checkWritableDirectories($options['directories']); 	break;
		}
	}
	
	/**
	 * @return int the current status
	 */
	public function getStatus(){
		return $this->status;
	}
	
	/**
	 * @return string the current message
	 */
	public function getMessage(){
		return $this->message;
	}
	
	/**
	 * Check that some directories are writable for the apache user (default : www-data)
	 * @params array $params (key:(string)extension name, value:(array)list of directories)
	 */
	protected function checkWritableDirectories ($params=array()){
		//$installedExtensions = $extensionManager = common_ext_ExtensionsManager::singleton();
		$errorCount = 0;
		foreach ($params as $extensionName => $directories){
			foreach ($directories as $directory) {
				if (!is_writable(REL_PATH.$directory)){
					// @todo whoami is not a recognized command in MS Windows!
					$this->message .= $directory." should be writable for the user ".exec('whoami')."<br/>";
					$errorCount++;
				}
			}
		}
		if ($errorCount == 0){
			$this->status = self::STATUS_VALID;
			$this->message = 'Read/Write system rights are correctly set.';
		}
		else{
			$this->status = self::STATUS_INVALID;
		}
	}
	
	/**
	 * Check the PHP Version and update the status and the message
	 * @param string $min version
	 * @param string|null $max version
	 */
	protected function checkPhpVersion($min, $max = null){
		$this->status = self::STATUS_INVALID;
		if(is_null($max)){
			$this->message = "Required PHP version is greater than {$min}. Your version is ".PHP_VERSION.".";
			if(version_compare(PHP_VERSION, $min, 'gt')){
				$this->status = self::STATUS_VALID;
			}
		}
		else{
			$this->message = "Required PHP version is greater than $min and less than $max. Your version is ".PHP_VERSION.".";
			if(version_compare(PHP_VERSION, $max, 'lt') && version_compare(PHP_VERSION, $min, 'gt')){
				$this->status = self::STATUS_VALID;
			}
		}
	}
	
	/**
	 * Check if a PHP extension is loaded and update the status and the message
	 * @param string $extensionName
	 */
	protected function checkPhpExtension($extensionName){
		switch(strtolower($extensionName)){
			case 'json':
			case 'dom':
			case 'spl':
				$this->message = 'PHP extension '.strtoupper($extensionName).' is required';
				(extension_loaded(strtolower($extensionName))) ? $this->status = self::STATUS_VALID : $this->status = self::STATUS_INVALID;
				break;
			case 'zip':
			case 'tidy': 
			case 'curl': 
				$this->message = 'PHP extension '.strtoupper($extensionName).' is strongly recomended';
				(extension_loaded(strtolower($extensionName))) ? $this->status = self::STATUS_VALID : $this->status = self::STATUS_INVALID;
				break;
			case 'gd':
				$this->message = 'PHP extension GD is optionnal';
				(extension_loaded('gd')) ? $this->status  = self::STATUS_VALID : $this->status  = self::STATUS_INVALID;
				break;
			case 'suhosin':
				$this->message 	= "Suhosin patch is optionnal but is not installed on your web server. If you use it, ".
								"set the directives suhosin.request.max_varname_length and suhosin.post.max_name_length to 128.";
				(extension_loaded('suhosin')) ? $this->status = self::STATUS_VALID : $this->status = self::STATUS_INVALID;
				break;
			case 'mysql':
			case 'mysqli':
			case 'pdo':
			case 'pdo_mysql':
			case 'pgsql':
				if (extension_loaded(strtolower($extensionName))) {
					$dbsystem = $extensionName == 'pgsql' ? 'PostgreSQL' : 'MySQL';
					$this->message = 'PHP extension for '.$dbsystem.' is available.';
					$this->status  = self::STATUS_VALID;
				} else {
					$this->message = 'PHP extension mysql, mysqli, pdo or pgsql is required';
					$this->status  = self::STATUS_INVALID;
				}
				break;
			case 'svn':
				$this->message = 'PHP extension svn is optionnal but it is recommended to version resources.';
				(extension_loaded(strtolower($extensionName))) ? $this->status  = self::STATUS_VALID : $this->status  = self::STATUS_INVALID;
				break;
			default :
				$this->message 	= "Unable to determine the status of PHP extension {$extensionName}.";
				$this->status	= self::STATUS_UNKNOW;
				break;	
		}
	}
	
	/**
	 * Check if an Apache module is loaded and update the status and the message
	 * @param string $moduleName
	 */
	protected function checkApacheMod($moduleName){
		
		switch(strtolower($moduleName)){
			case 'rewrite' : 
				
				$this->status  = self::STATUS_UNKNOW;
				$this->message = '';
				//check if the url rewriting is enabled by sending a cUrl request
				if(function_exists('curl_init')){
                                        //building test url:
					$infos = tao_install_utils_System::getInfos();
                                        ($infos['https']) ? $url = 'https://': $url = 'http://';
					$url .= tao_helpers_File::concat(array($infos['host'], $infos['folder'], '/test'));
                                        
					$curlHandler = curl_init();
					curl_setopt($curlHandler, CURLOPT_URL, $url);
					curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
					curl_exec($curlHandler);
					if(curl_errno($curlHandler) == 0){
						$code = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
						if($code == 302){
							$this->status =  self::STATUS_VALID;
						}
						else{
							$this->message = "Please make sure the mod_rewrite is available on your apache instance. Code $code received on by trying a rewrite on URL: $url";
						}
					}
					else{
						$this->message = "Unable to test the module, an error ocurred during the process.";
					}
					curl_close($curlHandler);
				}
				else{
					$this->message = "cUrl is required to test the mod rewrite.";
				}
				break;
			default :
				$this->message 	= "Unable to determine the status of apache module {$moduleName}.";
				$this->status	= self::STATUS_UNKNOW;
		}
	}
	
}
?>
