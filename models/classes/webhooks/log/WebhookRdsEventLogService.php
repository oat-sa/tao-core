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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\webhooks\task\WebhookTaskContext;
use oat\tao\model\mvc\error\HttpStatusCode;

class WebhookRdsEventLogService extends ConfigurableService implements WebhookEventLogInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeNetworkErrorLog(WebhookTaskContext $webhookTaskContext, $networkError = null)
    {
        $record = $this->applyContext($webhookTaskContext)
            ->setResultMessage(sprintf('Network error: %s', $networkError))
            ->setResult(WebhookEventLogRecord::RESULT_NETWORK_ERROR);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeInvalidHttpStatusLog(
        WebhookTaskContext $webhookTaskContext,
        $actualHttpStatusCode,
        $responseBody = null
    ) {
        $record = $this->applyContext($webhookTaskContext)
            ->setHttpStatusCode($actualHttpStatusCode)
            ->setResponseBody($responseBody)
            ->setResultMessage(sprintf('HTTP status code %d unexpected', $actualHttpStatusCode))
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_HTTP_STATUS);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeInvalidBodyFormat(WebhookTaskContext $webhookTaskContext, $responseBody = null)
    {
        $record = $this->applyContext($webhookTaskContext)
            ->setHttpStatusCode(HttpStatusCode::HTTP_OK)
            ->setResultMessage(sprintf('Invalid body format'))
            ->setResponseBody($responseBody)
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_BODY_FORMAT);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeInvalidAcknowledgementLog(
        WebhookTaskContext $webhookTaskContext,
        $responseBody,
        $actualAcknowledgement = null
    ) {
        $record = $this->applyContext($webhookTaskContext)
            ->setHttpStatusCode(HttpStatusCode::HTTP_OK)
            ->setResponseBody($responseBody)
            ->setAcknowledgementStatus($actualAcknowledgement)
            ->setResultMessage(sprintf('Acknowledgement "%s" unexpected', $actualAcknowledgement))
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_HTTP_STATUS);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeSuccessfulLog(WebhookTaskContext $webhookTaskContext, $responseBody, $acknowledgement)
    {
        $record = $this->applyContext($webhookTaskContext)
            ->setHttpStatusCode(HttpStatusCode::HTTP_OK)
            ->setResponseBody($responseBody)
            ->setAcknowledgementStatus($acknowledgement)
            ->setResultMessage('OK')
            ->setResult(WebhookEventLogRecord::RESULT_OK);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function storeInternalErrorLog(WebhookTaskContext $webhookTaskContext, $internalError = null)
    {
        $record = $this->applyContext($webhookTaskContext)
            ->setResultMessage(sprintf('Internal error: %s', $internalError))
            ->setResult(WebhookEventLogRecord::RESULT_INTERNAL_ERROR);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param WebhookTaskContext $webhookTaskContext
     * @return WebhookEventLogRecord
     * @throws \Exception
     */
    private function applyContext(WebhookTaskContext $webhookTaskContext)
    {
        $record = new WebhookEventLogRecord();

        $createdAt = new \DateTimeImmutable();

        $record
            ->setTaskId($webhookTaskContext->getTaskId())
            ->setCreatedAt($createdAt->getTimestamp());

        if ($taskParams = $webhookTaskContext->getWebhookTaskParams()) {
            $record->setEventId($taskParams->getEventId());
            $record->setEventName($taskParams->getEventName());
        }

        if ($webhookConfig = $webhookTaskContext->getWebhookConfig()) {
            $record->setHttpMethod($webhookConfig->getHttpMethod());
            $record->setEndpointUrl($webhookConfig->getUrl());
        }

        return $record;
    }

    /**
     * @return WebhookLogRepository
     */
    private function getRepository()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(WebhookLogRepository::SERVICE_ID);
    }
}
