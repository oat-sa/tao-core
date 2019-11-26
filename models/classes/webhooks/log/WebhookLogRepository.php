<?php

declare(strict_types=1);

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
 */

namespace oat\tao\model\webhooks\log;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

class WebhookLogRepository extends ConfigurableService implements WebhookLogRepositoryInterface
{
    use LoggerAwareTrait;

    public const OPTION_PERSISTENCE = 'persistence';

    /**
     * Constants for the database creation and data access
     */
    public const TABLE_NAME = 'webhook_event_log';

    public const COLUMN_ID = 'id';

    public const COLUMN_EVENT_ID = 'event_id';

    public const COLUMN_TASK_ID = 'task_id';

    public const COLUMN_WEBHOOK_ID = 'webhook_id';

    public const COLUMN_HTTP_METHOD = 'http_method';

    public const COLUMN_ENDPOINT_URL = 'endpoint_url';

    public const COLUMN_EVENT_NAME = 'event_name';

    public const COLUMN_HTTP_STATUS_CODE = 'http_status_code';

    public const COLUMN_RESPONSE_BODY = 'response_body';

    public const COLUMN_ACKNOWLEDGEMENT_STATUS = 'acknowledgement_status';

    public const COLUMN_CREATED_AT = 'created_at';

    public const COLUMN_RESULT = 'result';

    public const COLUMN_RESULT_MESSAGE = 'result_message';

    /** @var \common_persistence_SqlPersistence|null */
    private $persistence;

    /**
     * @inheritDoc
     * @throws InconsistencyConfigException
     */
    public function storeLog(WebhookEventLogRecord $webhookEventLog): void
    {
        $this->getPersistence()->insert(self::TABLE_NAME, [
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
        ]);
    }

    /**
     * @return \common_persistence_SqlPersistence
     * @throws InconsistencyConfigException
     */
    private function getPersistence()
    {
        if (! $this->persistence) {
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE) ?: 'default';
            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
            $persistence = $persistenceManager->getPersistenceById($persistenceId);
            if (! ($persistence instanceof \common_persistence_SqlPersistence)) {
                throw new InconsistencyConfigException(
                    "Configured persistence '${persistenceId}' is not sql persistence"
                );
            }
            $this->persistence = $persistence;
        }
        return $this->persistence;
    }
}
