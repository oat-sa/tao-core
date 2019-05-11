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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\actionSerializer;

use oat\oatbox\service\ConfigurableService;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\DBALException;

/**
 * Class RdsActionSerializer
 * @package oat\tao\model\actionSerializer
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RdsActionSerializer extends ConfigurableService implements ActionSerializer
{
    const OPTION_PERSISTENCE = 'persistence';
    const TABLE_NAME = 'action_serializer';
    const COLUMN_ACTION_ID = 'action_id';
    const COLUMN_VERSION = 'version';

    /**
     * @param $actionId
     * @return string
     */
    public function lock($actionId)
    {
        $this->logInfo(sprintf('Lock action %s', $actionId));

        try {
            $this->getPersistence()->getPlatForm()->getQueryBuilder()
                ->insert(self::TABLE_NAME)
                ->values([
                    self::COLUMN_ACTION_ID => '?'
                ])
                ->setParameters([$actionId])
                ->execute();
        } catch (DBALException $e) {
            $this->logInfo(sprintf('Lock record already exists for action %s', $actionId));
            //record already exists. Do nothing
        }

        $this->getPersistence()->getPlatForm()->beginTransaction();

        $queryBuilder = $this->getPersistence()->getPlatForm()->getQueryBuilder();
        $queryBuilder->select('*')
            ->from(self::TABLE_NAME)
            ->where(self::COLUMN_ACTION_ID .'=?');

        $sqlQuery = $queryBuilder->getSQL() .' '. $this->getPersistence()->getPlatForm()->getWriteLockSQL();
        $this->getPersistence()->query($sqlQuery, [$actionId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $actionId
     * @return string|void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \common_persistence_sql_SerializationException
     */
    public function unlock($actionId)
    {
        $this->logInfo(sprintf('Unlock action %s', $actionId));
        $this->getPersistence()->getPlatForm()->commit();
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    protected function getPersistence()
    {
        return $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }


    /**
     * Initialize log storage
     *
     * @param \common_persistence_Persistence $persistence
     * @return \common_report_Report
     */
    public static function install($persistence)
    {
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $schema->dropTable(self::TABLE_NAME);
            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
            foreach ($queries as $query) {
                $persistence->exec($query);
            }
        } catch (SchemaException $e) {
        }

        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $table = $schema->createTable(self::TABLE_NAME);
            $table->addOption('engine', 'InnoDB');
            $table->addColumn(static::COLUMN_ACTION_ID, 'string', ['length'=>255, 'notnull' => true]);
            $table->addColumn(static::COLUMN_VERSION, 'integer', ['notnull' => true, 'default' => 1]);
            $table->setPrimaryKey([static::COLUMN_ACTION_ID]);
        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('RDS scheduler storage successfully installed'));
    }
}