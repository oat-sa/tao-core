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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;

class tao_install_utils_DbalDbCreator
{
    /** @var array */
    private $dbConfiguration;

    /** @var Connection */
    private $connection;

    /** @var Schema */
	private $schema = null;

    /**
     * @param array $params
     * @throws tao_install_utils_Exception
     */
    public function __construct(array $params)
    {
        try {
            $this->connection = $this->buildDbalConnection($params);
            $this->dbConfiguration = $params;
            $this->buildSchema();
        } catch (Exception $e) {
            $this->connection = null;
            common_Logger::e($e->getMessage() . $e->getTraceAsString());
            throw new tao_install_utils_Exception('Unable to connect to the database ' . $params['dbname'] . ' with the provided credentials: ' . $e->getMessage());
        }
    }

    /**
     * @throws DBALException
     */
    private function createMysqlStatementsIndex(): void
    {
        $index = new \Doctrine\DBAL\Schema\Index('k_po', ['predicate(164)', 'object(164)']);
        $table = new \Doctrine\DBAL\Schema\Table('statements');
        $this->getSchemaManager()->createIndex($index, $table);
        $index = new \Doctrine\DBAL\Schema\Index('k_sp', ['predicate(164)', 'subject(164)']);
        $this->getSchemaManager()->createIndex($index, $table);
    }

    public function dbExists(string $dbName): bool
    {
        $sm = $this->getSchemaManager();
        common_Logger::d('Check if database with name \'' . $dbName . '\' exists for driver ' . $this->dbConfiguration['driver']);
        if ($this->dbConfiguration['driver'] === 'pdo_oci') {
            common_Logger::d('Oracle special query dbExist');
            return in_array(strtoupper($dbName), $sm->listDatabases());
        }

        return in_array($dbName, $sm->listDatabases());
    }

    public function tableExists(string $tableName){
    	$sm = $this->getSchemaManager();
    	return $sm->tableExists($tableName);
    }

    public function setDatabase(string $name): void
    {
        $this->connection->close();
        common_Logger::d('Switch to database ' . $name);
        $this->dbConfiguration['dbname'] = $name;
        $this->connection = $this->buildDbalConnection($this->dbConfiguration);
    }

    /**
     * @throws DBALException
     */
    private function buildDbalConnection(array $params): Connection
    {
        $config = new Configuration();
        return  DriverManager::getConnection($params, $config);
    }

    /**
     * @return string[]
     */
    public function listDatabases(): array
    {
        $sm = $this->getSchemaManager();
        return $sm->listDatabases();

    }

    private function createModelsSchema(): void
    {
        $table = $this->schema->createTable('models');
        $table->addColumn('modelid', 'string', ['length' => 25, 'notnull' => true]);
        $table->addColumn('modeluri', 'string', ['length' => 255]);
        $table->setPrimaryKey(['modelid']);
        $table->addOption('engine', 'MyISAM');
    }

    private function createStatementsSchena(): void
    {
        $table = $this->schema->createTable('statements');
        $table->addColumn('id', 'string', ['length' => 25, 'notnull' => true]);

        $table->addColumn('modelid', 'string', ['length' => 25, 'notnull' => true]);
        $table->addColumn('subject', 'string', ['length' => 255]);
        $table->addColumn('predicate', 'string', ['length' => 255]);
        if ($this->dbConfiguration['driver'] === 'pdo_oci') {
            $table->addColumn('object', 'string', ['length' => 4000]);
        } else {
            $table->addColumn('object', 'text', []);
        }
        $table->addColumn('l_language', 'string', ['length' => 255]);

        $table->addColumn('author', 'string', ['length' => 255]);
        $table->addColumn('epoch', 'string', ['notnull' => true]);

        $table->setPrimaryKey(['id']);

        if ($this->dbConfiguration['driver'] !== 'pdo_mysql') {
            $table->addIndex(['subject', 'predicate'], 'k_sp');
            common_Logger::d('driver is ' . $this->dbConfiguration['driver']);
            if ($this->dbConfiguration['driver'] !== 'pdo_sqlsrv'
                && $this->dbConfiguration['driver'] !== 'pdo_oci') {
                $table->addIndex(['predicate', 'object'], 'k_po');
            }
        }

        $table->addOption('engine', 'MyISAM');
    }

    private function createKeyValueStoreTable(): void
    {
        $table = $this->schema->createTable('kv_store');
        $table->addColumn('kv_id', 'string', ['notnull' => null, 'length' => 255]);
        $table->addColumn('kv_value', 'text', ['notnull' => null]);
        $table->addColumn('kv_time', 'integer', ['notnull' => null, 'length' => 30]);
        $table->setPrimaryKey(['kv_id']);
        $table->addOption('engine', 'MyISAM');
    }

    private function createSequenceUriProvider(): void
    {
        $table = $this->schema->createTable('sequence_uri_provider');
        $table->addColumn('uri_sequence', 'integer', ['notnull' => true, 'autoincrement' => true]);
        $table->addOption('engine', 'MyISAM');
        $table->setPrimaryKey(['uri_sequence']);
    }

    private function buildSchema(): Schema
    {
        if ($this->schema === null) {
            $this->schema = new Schema();
            $this->createModelsSchema();
            $this->createStatementsSchena();
            $this->createSequenceUriProvider();
            $this->createKeyValueStoreTable();
        }
        return $this->schema;
    }

    /**
     * @throws tao_install_utils_SQLParsingException
     */
    public function loadProc($file): void
    {
        $procedureCreator = new tao_install_utils_ProceduresCreator($this->dbConfiguration['driver'], $this->connection);
        $procedureCreator->load($file);
    }

    public function addModel($modelId, $namespace)
    {
        common_Logger::d('add modelid :' . $k . ' with NS :' . $v);
        $this->connection->insert("models", ['modelid' => $k, 'modeluri' => $v]);
    }

    public function addModels(): void
    {
        foreach ($this->modelArray as $k => $v) {
            $this->addModel();
        }
    }

    public function removeGenerisUser(): void
    {
        $this->connection->executeUpdate("DELETE FROM statements WHERE subject = ?", ['http://www.tao.lu/Ontologies/TAO.rdf#installator']);
    }

    /**
     * @throws DBALException
     */
    public function initTaoDataBase(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $queries = $this->schema->toSql($platform);
        foreach ($queries as $query) {
            $this->connection->executeUpdate($query);
        }
        if ($this->dbConfiguration['driver'] === 'pdo_mysql') {
            $this->createMysqlStatementsIndex();
        }
    }

    public function destroyTaoDatabase(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $queries = $this->schema->toDropSql($platform);
        foreach ($queries as $query) {
            $this->connection->executeUpdate($query);
        }
        //drop sequence
        $sm = $this->getSchemaManager();
        foreach ($sequences as $name) {
            $sm->dropSequence($name);
        }
    }

    public function listTables(): void
    {
        $sm = $this->getSchemaManager();
        return $sm->listTableNames();

    }

    public function dropDatabase($database): void
    {
        $sm = $this->getSchemaManager();
        $sm->dropDatabase($database);
    }

    public function createDatabase(string $database): void
    {
        $sm = $this->getSchemaManager();
        $escapedName = $sm->getDatabasePlatform()->quoteIdentifier($database);
        $sm->createDatabase($escapedName);
    }

    /**
     * @throws common_exception_Error
     */
    public function cleanDb(): void
    {
        $sm = $this->getSchemaManager();
        $tables = $sm->listTableNames();

        while (!empty($tables)) {
            $oldCount = count($tables);
            foreach (array_keys($tables) as $id) {
                $name = $tables[$id];
                try {
                    $sm->dropTable($name);
                    common_Logger::d('Droped table: ' . $name);
                    unset($tables[$id]);
                } catch (DBALException $e) {
                    common_Logger::w('Failed to drop: ' . $name);
                }
            }
            if (count($tables) == $oldCount) {
                throw new common_exception_Error('Unable to clean DB');
            }
        }
    }

    private function getSchemaManager(): AbstractSchemaManager
    {
        return $this->connection->getSchemaManager();
    }
}
