<?php
require_once 'generis/includes/adodb5/adodb-exceptions.inc.php';
require_once 'generis/includes/adodb5/adodb.inc.php';

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
	public function __construct( $host = 'localhost', $user = 'root', $pass = '', $driver = 'mysql', $dbName = "")
	{
		try{
			$this->adoConnection = NewADOConnection($driver);
			$this->adoConnection->Connect($host, $user, $pass);
			
			//if the database exists already, connect to it
			if ($this->dbExists($dbName)){
				$this->adoConnection->Close();
				$this->adoConnection->Connect($host, $user, $pass, $dbName);
			}
		}
		catch(ADODB_Exception $ae){
			$this->adoConnection = null;
			throw new tao_install_utils_Exception("Unable to connect to the database with the provided credentials.");
		}
		if($driver == 'mysql'){
			$this->adoConnection->Execute('SET NAMES utf8');
			// If the target Sgbd is mysql, force the engine to work with the standard identifier escape
			$this->adoConnection->Execute('SET SESSION SQL_MODE="ANSI_QUOTES"');
		}
	}
	
	/**
	 * Check if the database exists already
	 * @param string $name
	 */
	public function dbExists($dbName)
	{
		$databases = $this->adoConnection->MetaDatabases();
		if (in_array($dbName, $databases)){
			return true;
		}
		return false;
	}
	
	/**
	 * Clean database by droping all tables
	 * @param string $name
	 */
	public function cleanDb()
	{
		foreach ($this->adoConnection->MetaTables() as $table){
			$this->execute('DROP TABLE "'.$table.'";');
		}
	}
	
	/**
	 * Set up the database name
	 * @param string $name
	 * @throws tao_install_utils_Exception
	 */
	public function setDatabase($name)
	{
		if(!is_null($this->adoConnection)){
			try{
				$this->adoConnection->SelectDB($name);
			}
			catch(ADODB_Exception $ae){
				throw new tao_install_utils_Exception("Unable to connect to the database $name");
			}
		}
	}
	
	/**
	 * Load an SQL file into the current database
	 * Use it to load the database schema
	 * @param string $file path to the SQL file
	 * @param array repalce variable to replace into the file: array keys are search with {} around
	 * @throws tao_install_utils_Exception
	 */
	public function load($file, $replace = array())
	{
		
		//common file checks
		if(!file_exists($file) || !is_readable($file) || !preg_match("/\.sql$/", basename($file))){
			throw new tao_install_utils_Exception("Wrong SQL file: $file . CHECK IT!");
		}
		
		if ($handler = fopen($file, "r")){
			
			//parse file and get only usefull lines
			$ch = "";
			while (!feof ($handler)){
				$line = utf8_decode(fgets($handler));
		
				if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
					$ch = $ch.$line;
				}
			}
			
			//make replacements
			foreach($replace as $key => $value){
				$ch = str_replace('{'.strtoupper($key).'}', $value, $ch);
			}
			
			//explode and execute
			$requests = explode(";", $ch);
			
			try{
				foreach($requests as $index => $request){
					$requestTrim = trim($request);
					if(!empty($requestTrim)){
						$this->adoConnection->Execute($request);
					}
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
	public function execute($query)
	{
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
	public function __destruct()
	{
		if(!is_null($this->adoConnection)){
			$this->adoConnection->Close();
		}
	}
	
}
?>