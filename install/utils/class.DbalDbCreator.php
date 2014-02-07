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
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *
 * @author Lionel Lecaque  <lionel@taotesting.com>
 * @license GPLv2
 * @package tao
 * @subpackage install_utils
 *
 */


class tao_install_utils_DbalDbCreator {


    /**
     * @var array
     */
    private $dbConfiguration;
    /**
     * @var Doctrine\DBAL\Connection
     */
    private $connection;
	/**
	 * @var Doctrine\DBAL\Schema\Schema
	 */
	private $schema = null;
	
	/**
	 * @var array
	 */
	private $modelArray = array(
		3  => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#',
	    4  => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
	    5  => 'http://www.w3.org/2000/01/rdf-schema#',
	    6  => 'http://www.tao.lu/Ontologies/TAO.rdf#',
	    7  => 'http://www.tao.lu/Ontologies/generis.rdf#',
	    9  => 'http://www.tao.lu/Ontologies/TAOTest.rdf#',
	    10 => 'http://www.tao.lu/Ontologies/TAOItem.rdf#',
	    11 => 'http://www.tao.lu/Ontologies/TAOGroup.rdf#',
	    12 => 'http://www.tao.lu/Ontologies/TAOSubject.rdf#',
	    13 => 'http://www.tao.lu/Ontologies/TAOResult.rdf',
	    14 => 'http://www.tao.lu/Ontologies/TAODelivery.rdf#',
	    15 => 'http://www.tao.lu/Ontologies/TAODelivery.rdf#',
	    17 => 'http://www.tao.lu/middleware/Rules.rdf#'
	);

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
     * @param array $params
     * @throws tao_install_utils_Exception
     */
    public function __construct($params){
   		try{
            $this->connection = $this->buildDbalConnection($params);
            $this->dbConfiguration = $params;
            $this->buildSchema();

   		}
   		catch(Exception $e){
   			$this->connection = null;
            common_Logger::e($e->getMessage() . $e->getTraceAsString(), 'INSTALL');
   			throw new tao_install_utils_Exception('Unable to connect to the database ' . $params['dbname'] . ' with the provided credentials: ' . $e->getMessage());
   		}
   	}

    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $dbName
     */
    public function dbExists($dbName){
        $sm = $this->getSchemaManager();
        return in_array($dbName,$sm->listDatabases());
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
        $params = $this->connection->getParams();
        $this->connection = null;
        common_Logger::d('Switch to database ' . $name , 'INSTALL');
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
    private function createExtensionsSchema(){
    	$table = $this->schema->createTable("extensions");
    	
    	$table->addColumn("id", "string", array("length" => 25,"notnull" => true,'autoincrement' => true));
    	$table->addColumn("name", "string", array("length" => 150));
    	$table->addColumn("version", "string", array("length" => 5));
    	$table->addColumn("loaded", "integer");
    	$table->addColumn('"loadAtStartUp"', "integer");
    	$table->addColumn("ghost", "integer",array("notnull" => true,"default" => 0));
    	$table->setPrimaryKey(array("id"));
    	$table->addOption('engine' , 'MyISAM');
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createModelsSchema(){
	    $table = $this->schema->createTable("models");
	    $table->addColumn('"modelID"', "integer",array('autoincrement' => true));
	    $table->addColumn('"modelURI"', "string", array("length" => 255,"default" => null));
	    $table->addOption('engine' , 'MyISAM');
	    $table->setPrimaryKey(array('modelID'));
	    $table->addIndex(array('modelURI'),"idx_models_modelURI");
    }
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createStatementsSchena(){
    	$table = $this->schema->createTable("statements");
    	$table->addColumn('"modelID"', "integer",array("notnull" => true,"default" => 0));
    	$table->addColumn("subject", "string",array("length" => 255,"default" => null));
    	$table->addColumn("predicate", "string",array("length" => 255,"default" => null));
    	$table->addColumn("object", "text", array("default" => null,"notnull" => false));
    	$table->addColumn("l_language", "string",array("length" => 255,"default" => null,"notnull" => false));
    	$table->addColumn("id", "integer",array("notnull" => true,"autoincrement" => true));
    	$table->addColumn("author", "string",array("length" => 255,"default" => null,"notnull" => false));
    	$table->addColumn("stread", "string",array("length" => 255,"default" => null,"notnull" => false));
    	$table->addColumn("stedit", "string",array("length" => 255,"default" => null,"notnull" => false));
    	$table->addColumn("stdelete", "string",array("length" => 255,"default" => null,"notnull" => false));
    	$table->setPrimaryKey(array("id"));
    	$table->addOption('engine' , 'MyISAM');
    	$table->addColumn("epoch", "datetime" , array("notnull" => true));
    	$table->addIndex(array('modelID'),"idx_statements_modelID");
    	
    	if($this->dbConfiguration['driver'] != 'pdo_mysql'){
    	   $table->addIndex(array("subject","predicate"),"k_sp");
    	   $table->addIndex(array("predicate","object"),"k_po");
    	} 	
    
    }
    
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createClassToTable(){
    	$table = $this->schema->createTable("class_to_table");
    	$table->addColumn("id", "integer",array("notnull" => true,"autoincrement" => true));
    	$table->addColumn("uri", "string",array("notnull" => null));
    	$table->addColumn("table", "string",array("notnull" => null,"length" => 64));
    	$table->addColumn('"topClass"', "string",array("notnull" => null));
    	$table->addIndex(array("uri"),"idx_class_to_table_uri");
    	$table->addIndex(array("id"),"id");
    	$table->setPrimaryKey(array("id"));
    	$table->addOption('engine' , 'MyISAM');
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createClassAdditionalProp(){
    	$table = $this->schema->createTable("class_additional_properties");
    	$table->addColumn("class_id", "integer",array("notnull" => true));
    	$table->addColumn("property_uri", "string",array("length" => 255,"notnull" => null));
    	$table->setPrimaryKey(array("class_id","property_uri"));
    	$table->addOption('engine' , 'MyISAM');
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createResourceToTable(){
    	$table = $this->schema->createTable("resource_to_table");
    	$table->addColumn("id", "integer",array("notnull" => true,"autoincrement" => true));
    	$table->addColumn("uri", "string",array("notnull" => null));
    	$table->addColumn("table", "string",array("notnull" => null,"length" => 64));
    	$table->addIndex(array("uri"),"idx_resource_to_table_uri");
    	$table->addIndex(array("id"),"id");
    	$table->setPrimaryKey(array("id"));
    	$table->addOption('engine' , 'MyISAM');
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createResourceHasClass(){
    	$table = $this->schema->createTable("resource_has_class");
    	$table->addColumn("resource_id", "integer",array("notnull" => true));
   		$table->addColumn("class_id", "integer",array("notnull" => true));
   		$table->addOption('engine' , 'MyISAM');
    	$table->setPrimaryKey(array("resource_id","class_id"));
    	$table->addOption('engine' , 'MyISAM');
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function createSequenceUriProvider(){
    	$table = $this->schema->createTable("sequence_uri_provider");
    	$table->addColumn("uri_sequence", "integer",array("notnull" => true));
    	$table->addOption('engine' , 'MyISAM');
    	$table->setPrimaryKey(array("uri_sequence"));
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return Doctrine\DBAL\Schema\Schema
     */
    private  function buildSchema(){
    	if($this->schema == null){

    		$this->schema = new \Doctrine\DBAL\Schema\Schema() ;
			$this->createExtensionsSchema();
			$this->createModelsSchema();
			$this->createStatementsSchena();
			$this->createResourceToTable();
			$this->createResourceHasClass();
			$this->createClassToTable();
			$this->createClassAdditionalProp();
			$this->createSequenceUriProvider();
    	}
    	return $this->schema;
    	
    	
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $file
     */
    public function loadProc($file){
        
        $procedureCreator = new tao_install_utils_ProceduresCreator($this->dbConfiguration['driver'],$this->connection);
        $procedureCreator->load($file);

    }
    

    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function addModels(){
        foreach ($this->modelArray as $k => $v){
            $this->connection->insert("models" , array('modelID' => $k , 'modelURI' => $v ));
        }
    } 
    
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function removeGenerisUser(){
        
    }
    
    /**
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function initTaoDataBase(){
    	$platform = $this->connection->getDatabasePlatform();
    	$queries = $this->schema->toSql($platform);
    	foreach ($queries as $query){
    	   	$this->connection->executeUpdate($query);
    	}
    	if($this->dbConfiguration['driver'] == 'pdo_mysql'){
    	    $this->createMysqlStatementsIndex();
    	}
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

    public function createDatabase($database){
        $sm = $this->getSchemaManager();
        return $sm->createDatabase($database);
    }
    

    public function cleanDb(){
        $sm = $this->getSchemaManager();
        $tables = $sm->listTableNames();
        foreach($tables as $name){
            $sm->dropTable($name);
        }
        
        //drop sequence

    }

    /**
     * @return Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private function getSchemaManager()
    {
        return $this->connection->getSchemaManager();

    }


}