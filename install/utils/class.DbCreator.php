<?php
require_once dirname(__FILE__).'/../../includes/adodb/adodb-exceptions.inc.php';
require_once dirname(__FILE__).'/../../includes/adodb/adodb.inc.php';

/**
 * Dedicated database wrapper used for database installation
 * @see ADOConnection
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 *
 */
class tao_install_utils_DbCreator{
	
	/**
	 * @var ADOConnection
	 */
	protected $adoConnection = null;
	
	/**
	 * The constructor initialize the connection.
	 * It's a good way to test the connection by catching the exception
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $driver
	 * @throws tao_install_utils_Exception
	 */
	public function __construct( $host = 'localhost', $user = 'root', $pass = '', $driver = 'mysql'){
		
		$this->adoConnection = &NewADOConnection($driver);
		if(!$this->adoConnection->Connect($host, $user, $pass)){
			$this->adoConnection = null;
			throw new tao_install_utils_Exception("Unable to connect to the database with the provided credentials.");
		}
		if($driver == 'mysql')
			$this->adoConnection->Execute('SET NAMES utf8');
	}
	
	/**
	 * Load an SQL file into the current database
	 * Use it to load the database schema
	 * @param string $file path to the SQL file
	 * @throws tao_install_utils_Exception
	 */
	public function load($file){
		
		if(!file_exists($file) || !is_readable($file) || !preg_match("/\.sql$/", basename($file))){
			throw new tao_install_utils_Exception("Wrong SQL file: $file . CHECK IT!");
		}
		
		if ($handler = fopen($file, "r")){
			$ch = "";
			while (!feof ($handler)){
				$line = utf8_decode(fgets($handler));
		
				if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
					$ch = $ch.$line;
				}
			}
	
			$requests = explode(";", $ch);
			unset($requests[count($requests)-1]);
			try{
				foreach($requests as $index => $request){
					$this->adoConnection->Execute($request);
				}
			}
			catch(Exception $e){
				throw new tao_install_utils_Exception("Error executing query #$index : $request . ".$e->getMessage());
			}
			fclose($handler);
		}
	}
	
	/**
	 * Execute an SQL query
	 * @param string $query
	 * @throws tao_install_utils_Exception
	 */
	public function execute($query){
		if(!empty($query)){
			try{
				$this->adoConnection->Execute($query);
			}
			catch(Exception $e){
				throw new tao_install_utils_Exception("Error executing query : $query . ".$e->getMessage());
			}
		}
	}
	
	/**
	 * Close the connection when the wrapper is destructed
	 */
	public function __destruct(){
		if(!is_null($this->adoConnection)){
			$this->adoConnectio->Close();
		}
	}
	
}
?>