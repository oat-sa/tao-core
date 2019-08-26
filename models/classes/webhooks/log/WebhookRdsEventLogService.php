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

class WebhookRdsEventLogService extends ConfigurableService implements WebhookEventLogInterface
{
    const HTTP_OK_STATUS_CODE = '200';

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param string|null $networkError
     * @throws \Exception
     */
    public function storeNetworkErrorLog($eventId, $taskId, $parentTaskId, $networkError = null)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setResultMessage(sprintf('Network error: %s', $networkError))
            ->setResult(WebhookEventLogRecord::RESULT_NETWORK_ERROR);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param int $actualHttpStatusCode
     * @throws \Exception
     */
    public function storeInvalidHttpStatusLog($eventId, $taskId, $parentTaskId, $actualHttpStatusCode)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setHttpStatusCode($actualHttpStatusCode)
            ->setResultMessage(sprintf('HTTP status code %d unexpected', $actualHttpStatusCode))
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_HTTP_STATUS);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param string|null $responseBody
     * @throws \Exception
     */
    public function storeInvalidBodyFormat($eventId, $taskId, $parentTaskId, $responseBody = null)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setHttpStatusCode(self::HTTP_OK_STATUS_CODE)
            ->setResultMessage(sprintf('Invalid body format'))
            ->setResponseBody($responseBody)
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_BODY_FORMAT);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param string $responseBody
     * @param string|null $actualAcknowledgement
     * @throws \Exception
     */
    public function storeInvalidAcknowledgementLog($eventId, $taskId, $parentTaskId, $responseBody, $actualAcknowledgement = null)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setHttpStatusCode(self::HTTP_OK_STATUS_CODE)
            ->setResponseBody($responseBody)
            ->setAcknowledgementStatus($actualAcknowledgement)
            ->setResultMessage(sprintf('Acknowledgement "%s" unexpected', $actualAcknowledgement))
            ->setResult(WebhookEventLogRecord::RESULT_INVALID_HTTP_STATUS);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param string $responseBody
     * @param string $acknowledgement
     * @throws \Exception
     */
    public function storeSuccessfulLog($eventId, $taskId, $parentTaskId, $responseBody, $acknowledgement)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setHttpStatusCode(self::HTTP_OK_STATUS_CODE)
            ->setResponseBody($responseBody)
            ->setAcknowledgementStatus($acknowledgement)
            ->setResultMessage('OK')
            ->setResult(WebhookEventLogRecord::RESULT_OK);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @param string|null $internalError
     * @throws \Exception
     */
    public function storeInternalErrorLog($eventId, $taskId, $parentTaskId, $internalError = null)
    {
        $record = $this->createRecordSkeleton($eventId, $taskId, $parentTaskId)
            ->setResultMessage(sprintf('Internal error: %s', $internalError))
            ->setResult(WebhookEventLogRecord::RESULT_INTERNAL_ERROR);

        $this->getRepository()->storeLog($record);
    }

    /**
     * @param string $eventId
     * @param string $taskId
     * @param string $parentTaskId
     * @return WebhookEventLogRecord
     * @throws \Exception
     */
    private function createRecordSkeleton($eventId, $taskId, $parentTaskId)
    {
        $record = new WebhookEventLogRecord();

        $createdAt = new \DateTimeImmutable();

        $record
            ->setEventId($eventId)
            ->setTaskId($taskId)
            ->setParentTaskId($parentTaskId)
            ->setCreatedAt($createdAt->getTimestamp());

        return $record;
    }

    /**
     * @return WebhookLogRepository
     */
    private function getRepository()
    {
        return $this->getServiceLocator()->get(WebhookLogRepository::SERVICE_ID);
    }
}
