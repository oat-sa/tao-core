<?php
require_once 'generis/includes/adodb5/adodb-exceptions.inc.php';
require_once 'generis/includes/adodb5/adodb.inc.php';

/**
 * Dedicated database wrapper used for database creation.
 * Its purpose is to establish a connection with a database
 * and load a given SQL file containing simple statements.
 * 
 * Please note that this Database Creator does not support
 * complex SQL statements such as Stored Procedure or Functions
 * creations. If you need to load complex statements such as
 * Stored Procedure or Function declarations, use tao_install_utils_SpCreator.
 * 
 * @see ADOConnection
 * @see tao_install_utils_SpConnector
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 * @author Jerome BOGAERTS <jerome.bogaerts@tudor.lu>
 *
 */
class tao_install_utils_DbCreator extends tao_install_utils_DbConnector{

	/**
	 * Load a given SQL file containing simple statements.
	 * SQL files containing Stored Procedure or Function declarations
	 * are not supported. Use tao_install_utils_SpConnector instead.
	 * 
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