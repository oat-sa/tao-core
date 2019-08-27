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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\webhooks\log\WebhookLogRepository;
use Doctrine\DBAL\Types\Type;

/**
 * Deploys the webhook_event_log schema
 *
 * Class CreateWebhookEventLog
 * @package oat\taoQtiTest\scripts\install
 */
class CreateWebhookEventLogTable extends AbstractAction
{
    /**
     * @param array $params
     * @return Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';
        /** @var \common_persistence_Persistence $persistence */
        $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_KEY)->getPersistenceById($persistenceId);

        /** @var \common_persistence_sql_dbal_SchemaManager $schemaManager */
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        if ($schema->hasTable(WebhookLogRepository::TABLE_NAME)) {
            return new Report(Report::TYPE_INFO, 'Webhook event log table already created, exit');
        }

        $logTable = $schema->createTable(WebhookLogRepository::TABLE_NAME);
        $logTable->addOption('engine', 'InnoDB');

        $logTable->addColumn(WebhookLogRepository::COLUMN_ID, Type::INTEGER, array('autoincrement' => true));
        $logTable->addColumn(WebhookLogRepository::COLUMN_EVENT_ID, Type::STRING, array('notnull' => false, 'length' => 255));
        $logTable->addColumn(WebhookLogRepository::COLUMN_TASK_ID, Type::STRING, array('notnull' => false, 'length' => 255));
        $logTable->addColumn(WebhookLogRepository::COLUMN_PARENT_TASK_ID, Type::STRING, array('notnull' => true, 'length' => 255));
        $logTable->addColumn(WebhookLogRepository::COLUMN_HTTP_STATUS_CODE, Type::SMALLINT, array('notnull' => false));
        $logTable->addColumn(WebhookLogRepository::COLUMN_RESPONSE_BODY, Type::TEXT, array('notnull' => false));
        $logTable->addColumn(WebhookLogRepository::COLUMN_ACKNOWLEDGEMENT_STATUS, Type::STRING, array('notnull' => false, 'length' => 255));
        $logTable->addColumn(WebhookLogRepository::COLUMN_CREATED_AT, Type::INTEGER, array('notnull' => true));
        $logTable->addColumn(WebhookLogRepository::COLUMN_RESULT, Type::STRING, array('notnull' => true, 'length' => 255));
        $logTable->addColumn(WebhookLogRepository::COLUMN_RESULT_MESSAGE, Type::TEXT, array('notnull' => false));

        $logTable->addUniqueIndex(
            [WebhookLogRepository::COLUMN_EVENT_ID],
            'IDX_' . WebhookLogRepository::TABLE_NAME . '_event_id');

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return new Report(Report::TYPE_SUCCESS, 'RDS schema for WebhookLogRepository is now installed');
    }
}
