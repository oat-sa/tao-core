<?php

/**
 * Dedicated database wrapper used for database installation
 * @see ADOConnection
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 * @author Jerome BOGAERTS <jerome.bogaerts@tudor.lu>
 *
 */
abstract class tao_install_utils_DbConnector{
	
	/**
	 * @var PDO
	 */
	protected $pdo = null;
	
	/**
	 * @var driver
	 */
	protected $driver = '';
	
	/**
	 * @var user
	 */
	protected $user = '';
	
	/**
	 * @var pass
	 */
	protected $pass = '';
	
	/**
	 * @var host
	 */
	protected $host = '';
	
	/**
	 * @var $options
	 */
	protected $options = array();
	
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
		$this->driver = strtolower($driver);
		$this->user = $user;
		$this->pass = $pass;
		$this->host = $host;
		
		try{
	        $dsn = $driver . ':host=' . $host . ';charset=utf8';
	        $this->options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
	        					   PDO::ATTR_PERSISTENT => false,
	        					   PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	        					   PDO::ATTR_EMULATE_PREPARES => false);
	        				 
	     	$this->pdo = new PDO($dsn, $this->user, $this->pass, $this->options);
			$this->afterConnect();
	     	
			//if the database exists already, connect to it
			if ($this->dbExists($dbName)){
				$this->setDatabase($dbName);
			}
		}
		catch(PDOException $e){
			$this->pdo = null;
			throw new tao_install_utils_Exception("Unable to connect to the database '${dbName}' with the provided credentials: " . $e->getMessage());
		}
	}
	
	/**
	 * Check if the database exists already
	 * @param string $name
	 */
	public function dbExists($dbName)
	{
		$result = $this->pdo->query('SHOW DATABASES');
		$databases = array();
		while($db = $result->fetchColumn(0)){
			$databases[] = $db;
		}
		
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
		$tables = array();
		$result = $this->pdo->query('SHOW TABLES');
		
		while ($t = $result->fetchColumn(0)){
			$tables[] = $t;
		}

		foreach ($tables as  $t){
			$this->pdo->exec("DROP TABLE \"${t}\"");
		}
	}
	
	/**
	 * Set up the database name
	 * @param string $name
	 * @throws tao_install_utils_Exception
	 */
	public function setDatabase($name)
	{
		// We have to reconnect with PDO :/
		try{
			$this->pdo = null;
			$dsn = $this->driver . ':dbname=' . $name . ';host=' . $this->host. ';charset=utf8';
			$this->pdo = new PDO($dsn, $this->user, $this->pass, $this->options);
			$this->afterConnect();
		}
		catch (PDOException $e){
			throw new tao_install_utils_Exception("Unable to set database '${name}': " . $e->getMessage() . "");
		}
	}
	
	public function createDatabase($name){
		$this->pdo->exec('CREATE DATABASE "' . $name . '"');
	}
	
	/**
	 * Load an SQL file into the current database. This method musb be implemented by a DbConnector implementation.
	 * Use it to load the database schema
	 * @param string $file path to the SQL file
	 * @param array repalce variable to replace into the file: array keys are search with {} around
	 * @throws tao_install_utils_Exception
	 */
	abstract public function load($file, $replace = array());
	
	public function execute($query){
		$this->pdo->exec($query);
	}
	
	/**
	 * Close the connection when the wrapper is destructed
	 */
	public function __destruct()
	{
		if(!is_null($this->pdo)){
			$pdo = null;
		}
	}
	
	protected function afterConnect(){
		if ($this->driver == 'mysql'){
			$this->pdo->exec('SET NAMES utf8');
			// If the target rdbms is mysql, force the engine to work with the standard identifier escape
			$this->pdo->exec('SET SESSION SQL_MODE="ANSI_QUOTES"');
		}
	}
}
?>