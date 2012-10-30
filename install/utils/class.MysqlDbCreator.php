<?php

/**
 * Dedicated database wrapper used for database creation in
 * a MySQL context.
 * 
 * @see PDO
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 * @author Jerome BOGAERTS <jerome.bogaerts@tudor.lu>
 *
 */
class tao_install_utils_MysqlDbCreator extends tao_install_utils_DbCreator{
	
	public function chooseSQLParsers(){
		$this->setSQLParser(new tao_install_utils_SimpleSQLParser());
		$this->setProcSQLParser(new tao_install_utils_MysqlProceduresParser());
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
	
	public function createDatabase($name){
		$this->pdo->exec('CREATE DATABASE "' . $name . '"');
		$this->setDatabase($name);
	}
	
	protected function afterConnect(){
		$this->pdo->exec('SET SESSION SQL_MODE="ANSI_QUOTES"');
	}
}
?>