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
 */

namespace oat\tao\model\webhooks\log;

use oat\tao\model\webhooks\task\WebhookTaskContext;

interface WebhookEventLogInterface
{
    const SERVICE_ID = 'tao/webhookEventLog';

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param string|null $networkError
     */
    public function storeNetworkErrorLog(WebhookTaskContext $webhookTaskContext, $networkError = null);

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param int $actualHttpStatusCode
     * @param string|null $responseBody
     */
    public function storeInvalidHttpStatusLog(
        WebhookTaskContext $webhookTaskContext,
        $actualHttpStatusCode,
        $responseBody = null
    );

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param string|null $responseBody
     */
    public function storeInvalidBodyFormat(WebhookTaskContext $webhookTaskContext, $responseBody = null);

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param string $responseBody
     * @param string|null $actualAcknowledgement
     */
    public function storeInvalidAcknowledgementLog(WebhookTaskContext $webhookTaskContext, $responseBody, $actualAcknowledgement = null);

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param string $responseBody
     * @param string $acknowledgement
     */
    public function storeSuccessfulLog(WebhookTaskContext $webhookTaskContext, $responseBody, $acknowledgement);

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @param string|null $internalError
     */
    public function storeInternalErrorLog(WebhookTaskContext $webhookTaskContext, $internalError = null);
}
