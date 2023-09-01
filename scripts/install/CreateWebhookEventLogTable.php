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

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\webhooks\log\WebhookLogRepositoryInterface;

/**
 * Deploys the webhook_event_log schema
 */
class CreateWebhookEventLogTable extends AbstractAction
{
    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params)
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);

        /** @var WebhookLogRepositoryInterface $webhookLogRepository */
        $webhookLogRepository = $this->getServiceLocator()->get(WebhookLogRepositoryInterface::SERVICE_ID);

        $schemaCollection = $persistenceManager->getSqlSchemas();
        $webhookLogRepository->provideSchema($schemaCollection);

        $persistenceManager->applySchemas($schemaCollection);

        return new Report(Report::TYPE_SUCCESS, 'RDS schema for WebhookLogRepository is now installed');
    }
}
