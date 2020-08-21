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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks\log;

use Doctrine\DBAL\Types\Types;
use oat\generis\Helper\UuidPrimaryKeyTrait;
use oat\generis\persistence\sql\SchemaCollection;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class NewSqlWebhookLogRepository extends AbstractWebhookLogRepository
{
    use UuidPrimaryKeyTrait;

    /**
     * @throws InconsistencyConfigException
     */
    public function storeLog(WebhookEventLogRecord $webhookEventLog): void
    {
        $this->getPersistence()->insert(
            self::TABLE_NAME,
            [
                self::COLUMN_ID => $this->getUniquePrimaryKey(),
                self::COLUMN_EVENT_ID => $webhookEventLog->getEventId(),
                self::COLUMN_TASK_ID => $webhookEventLog->getTaskId(),
                self::COLUMN_WEBHOOK_ID => $webhookEventLog->getWebhookId(),
                self::COLUMN_HTTP_METHOD => $webhookEventLog->getHttpMethod(),
                self::COLUMN_ENDPOINT_URL => $webhookEventLog->getEndpointUrl(),
                self::COLUMN_EVENT_NAME => $webhookEventLog->getEventName(),
                self::COLUMN_HTTP_STATUS_CODE => $webhookEventLog->getHttpStatusCode(),
                self::COLUMN_RESPONSE_BODY => $webhookEventLog->getResponseBody(),
                self::COLUMN_ACKNOWLEDGEMENT_STATUS => $webhookEventLog->getAcknowledgementStatus(),
                self::COLUMN_CREATED_AT => $webhookEventLog->getCreatedAt(),
                self::COLUMN_RESULT => $webhookEventLog->getResult(),
                self::COLUMN_RESULT_MESSAGE => $webhookEventLog->getResultMessage(),
            ]
        );
    }

    public function provideSchema(SchemaCollection $schemaCollection)
    {
        $schema = $schemaCollection->getSchema($this->getOption(self::OPTION_PERSISTENCE));

        $logTable = $schema->createTable(self::TABLE_NAME);
        $logTable->addOption('engine', 'InnoDB');

        $logTable->addColumn(self::COLUMN_ID, Types::STRING, ['notnull' => true, 'length' => 36]);
        $logTable->addColumn(self::COLUMN_EVENT_ID, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_TASK_ID, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_WEBHOOK_ID, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_HTTP_METHOD, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_ENDPOINT_URL, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_EVENT_NAME, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_HTTP_STATUS_CODE, Types::SMALLINT, ['notnull' => false]);
        $logTable->addColumn(self::COLUMN_RESPONSE_BODY, Types::TEXT, ['notnull' => false]);
        $logTable->addColumn(self::COLUMN_ACKNOWLEDGEMENT_STATUS, Types::STRING, ['notnull' => false, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_CREATED_AT, Types::INTEGER, ['notnull' => true]);
        $logTable->addColumn(self::COLUMN_RESULT, Types::STRING, ['notnull' => true, 'length' => 255]);
        $logTable->addColumn(self::COLUMN_RESULT_MESSAGE, Types::TEXT, ['notnull' => false]);

        $logTable->setPrimaryKey([self::COLUMN_ID]);

        $logTable->addIndex([self::COLUMN_EVENT_ID], 'IDX_' . self::TABLE_NAME . '_event_id');

        $logTable->addIndex([self::COLUMN_WEBHOOK_ID], 'IDX_' . self::TABLE_NAME . '_webhook_id');

        $logTable->addIndex([self::COLUMN_CREATED_AT], 'IDX_' . self::TABLE_NAME . '_created_at');
    }
}
