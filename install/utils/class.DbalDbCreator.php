<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Lionel Lecaque  <lionel@taotesting.com>
 * @license GPLv2
 * @package tao
 *
 */

use Doctrine\DBAL\DBALException;

class tao_install_utils_DbalDbCreator
{
    /**
     * @var array
     */
    private $dbConfiguration;

    /** @var string */
    private $driverName;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private $connection;

	/**
	 * @var Doctrine\DBAL\Schema\Schema
	 */
	private $schema = null;

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param array $params
     * @throws tao_install_utils_Exception
     */
    public function __construct($params){
   		try{
            $this->connection = $this->buildDbalConnection($params);
            $this->dbConfiguration = $params;
            $this->driverName = $this->findDriverName();
            $this->buildSchema();
        } catch(Exception $e){
   			$this->connection = null;
            common_Logger::e($e->getMessage() . $e->getTraceAsString());
   			throw new tao_install_utils_Exception('Unable to connect to the database ' . $params['dbname'] . ' with the provided credentials: ' . $e->getMessage());
   		}
   	}

    /**
     * Finds the driver name, without changing the original configuration.
     *
     * @return string
     * @throws tao_install_utils_Exception when no or an unknown driver is provided.
     */
   	protected function findDriverName()
    {
        if (isset($this->dbConfiguration['driver'])) {
            return $this->dbConfiguration['driver'];
        }

        $driverNames = [
        ];

        if (isset($this->dbConfiguration['driverClass'])) {
            $driverClass = $this->dbConfiguration['driverClass'];
            if (isset($driverNames[$driverClass])) {
                return $driverNames[$driverClass];
            }
            throw new tao_install_utils_Exception('Unknown database driver "' . $driverClass . '".');
        }

        throw new tao_install_utils_Exception('No database driver found. Please specify either "driver" or "driverClass" key in DBAL configuration array.');
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $dbName
     */
    public function dbExists($dbName){
        $sm = $this->getSchemaManager();
		common_Logger::d(sprintf('Check if database with name "%s" exists for driver "%s".', $dbName, $this->driverName));
        if($this->driverName == 'pdo_oci'){
        	common_Logger::d('Oracle special query dbExist');
        	return in_array(strtoupper($dbName),$sm->listDatabases());
        }
        else {
        	return in_array($dbName,$sm->listDatabases());
        }
    }
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $tableName
     */
    public function tableExists($tableName){
    	$sm = $this->getSchemaManager();
    	return $sm->tableExists($tableName);
    }


    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $name
     */
    public function setDatabase($name){
        $this->connection->close();
        common_Logger::d('Switch to database ' . $name);
        $this->dbConfiguration['dbname'] = $name;
        $this->connection = $this->buildDbalConnection($this->dbConfiguration);

    }

    /**
     * @param $params
     * @return \Doctrine\DBAL\Connection
     */
    private function buildDbalConnection($params)
    {
        $config = new Doctrine\DBAL\Configuration();
        return  \Doctrine\DBAL\DriverManager::getConnection($params, $config);
    }


    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function listDatabases(){
    	$sm = $this->getSchemaManager();
    	return $sm->listDatabases();

    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createModelsSchema(){
	    $table = $this->schema->createTable('models');
	    $table->addColumn('modelid', 'string', ['length' => 23]);
	    $table->addColumn('modeluri', 'string', ['length' => 255]);
        $table->setPrimaryKey(array('modelid'));
        $table->addOption('engine' , 'MyISAM');
    }
    /**
     * @author 'Lionel Lecaque, <lionel@taotesting.com>'
     */
    private function createStatementsSchena(){
    	$table = $this->schema->createTable('statements');
    	$table->addColumn('id', 'string', ['length' => 23]);

    	$table->addColumn('modelid', 'string', ['length' => 23]);
    	$table->addColumn('subject', 'string', ['length' => 255]);
    	$table->addColumn('predicate', 'string', ['length' => 255]);
    	if($this->driverName == 'pdo_oci' ) {
    		$table->addColumn('object', 'string', ['length' => 4000, 'notnull' => false]);
    	} else {
    		$table->addColumn('object', 'text', [ 'notnull' => false]);
    	}
        $table->addColumn('l_language', 'string', ['length' => 255, 'notnull' => false]);

    	$table->addColumn('author', 'string', ['length' => 255, 'notnull' => false]);
    	$table->addColumn('epoch', 'string' , []);

    	$table->setPrimaryKey(['id']);

    	if($this->driverName != 'pdo_mysql'){
    	   	$table->addIndex(['subject', 'predicate'], 'k_sp');
    		common_Logger::d('driver is ' . $this->driverName);
    	   	if($this->driverName != 'pdo_sqlsrv'
    	   			&& $this->driverName != 'pdo_oci'){
    	   		$table->addIndex(['predicate', 'object'], 'k_po');
    	   	}
    	}

        $table->addOption('engine' , 'MyISAM');
    }


    /**
     *
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createKeyValueStoreTable(){
        $table = $this->schema->createTable("kv_store");
        $table->addColumn('kv_id',"string",array("notnull" => null,"length" => 255));
        $table->addColumn('kv_value',"text",array("notnull" => null));
        $table->addColumn('kv_time',"integer",array("notnull" => null,"length" => 30));
        $table->setPrimaryKey(array("kv_id"));
        $table->addOption('engine' , 'MyISAM');
    }


    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createSequenceUriProvider(){
    	$table = $this->schema->createTable("sequence_uri_provider");
    	$table->addColumn("uri_sequence", "integer",array("notnull" => true,"autoincrement" => true));
    	$table->addOption('engine' , 'MyISAM');
    	$table->setPrimaryKey(array("uri_sequence"));

    	//$this->schema->createSequence('sequence_uri_provider_uri_sequence_seq');
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return Doctrine\DBAL\Schema\Schema
     */
    private  function buildSchema(){
    	if($this->schema == null){

    		$this->schema = new \Doctrine\DBAL\Schema\Schema() ;
			$this->createModelsSchema();
			$this->createStatementsSchena();
// 			$this->createResourceToTable();
// 			$this->createResourceHasClass();
// 			$this->createClassToTable();
// 			$this->createClassAdditionalProp();
			$this->createSequenceUriProvider();
			$this->createKeyValueStoreTable();
    	}
    	return $this->schema;


    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $file
     */
    public function loadProc($file){

        $procedureCreator = new tao_install_utils_ProceduresCreator($this->driverName, $this->connection);
        $procedureCreator->load($file);

    }


    public function addModel($modelId,$namespace){
        common_Logger::d('add modelid :' . $k . ' with NS :' . $v);
        $this->connection->insert("models" , array('modelid' => $k , 'modeluri' => $v ));
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function addModels(){
        foreach ($this->modelArray as $k => $v){
            $this->addModel();
        }
    }


    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function removeGenerisUser(){
       $this->connection->executeUpdate("DELETE FROM statements WHERE subject = ?" , array('http://www.tao.lu/Ontologies/TAO.rdf#installator'));
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function initTaoDataBase(){
    	$platform = $this->connection->getDatabasePlatform();
    	$queries = $this->schema->toSql($platform);
    	foreach ($queries as $query){
    	   	$this->connection->exec($query);
    	}
    	if($this->driverName == 'pdo_mysql'){
    	    $this->createMysqlStatementsIndex();
    	}
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createMysqlStatementsIndex(){
        $index = new \Doctrine\DBAL\Schema\Index('k_po',array('predicate(164)','object(164)'));
        $table = new \Doctrine\DBAL\Schema\Table('statements');
        $this->getSchemaManager()->createIndex($index,$table);
        $index = new \Doctrine\DBAL\Schema\Index('k_sp',array('predicate(164)','subject(164)'));
        $this->getSchemaManager()->createIndex($index,$table);
    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function destroyTaoDatabase(){
    	$platform = $this->connection->getDatabasePlatform();
    	$queries = $this->schema->toDropSql($platform);
    	foreach ($queries as $query){
    		$this->connection->executeUpdate($query);
    	}
    	//drop sequence
    	$sm = $this->getSchemaManager();
    	$sequences = $sm->listSequences();
    	foreach($sequences as $name){
    		$sm->dropSequence($name);
    	}


    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function listTables(){
    	$sm = $this->getSchemaManager();
    	return $sm->listTableNames();

    }

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $database
     */
    public function dropDatabase($database){
        $sm = $this->getSchemaManager();
        return $sm->dropDatabase($database);

    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param string $database
     */
    public function createDatabase($database){
        $sm = $this->getSchemaManager();
        $escapedName = $sm->getDatabasePlatform()->quoteIdentifier($database);
        return $sm->createDatabase($escapedName);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function cleanDb(){
        $sm = $this->getSchemaManager();
        $platform = $this->connection->getDatabasePlatform();
        $tables = $sm->listTableNames();

        while (!empty($tables)) {
            $oldCount = count($tables);
            foreach(array_keys($tables) as $id){
                $name = $tables[$id];
                try {
                    $sm->dropTable($name);
                    common_Logger::d('Droped table: '  . $name);
                    unset($tables[$id]);
                } catch (DBALException $e) {
                    common_Logger::w('Failed to drop: '  . $name);
                }
            }
            if (count($tables) == $oldCount) {
                throw new common_exception_Error('Unable to clean DB');
            }
        }
    }

    /**
     * @return Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private function getSchemaManager()
    {
        return $this->connection->getSchemaManager();

    }


}
