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

class WebhookEventLogRecord
{
    /** @var string|null */
    private $eventId;

    /** @var string|null */
    private $taskId;

    /** @var string|null */
    private $parentTaskId;

    /**
     * @var string|null
     * @example port refused (in case of network error)
     * @example unknown host (in case of network error)
     * @example Undefined index: key (in case of internal error)
     */
    private $resultMessage;

    /** @var int|null */
    private $httpStatusCode;

    /** @var string|null */
    private $responseBody;

    /** @var string|null */
    private $acknowledgementStatus;

    /** @var string|null */
    private $createdAt;

    /** @var string|null */
    private $result;

    const RESULT_INTERNAL_ERROR = 'internal_error';
    const RESULT_NETWORK_ERROR = 'network_error';
    const RESULT_INVALID_BODY_FORMAT = 'invalid_body_format';
    const RESULT_INVALID_HTTP_STATUS = 'invalid_http_status';
    const RESULT_INVALID_ACKNOWLEDGEMENT = 'invalid_acknowledgement';
    const RESULT_OK = 'ok';

    /**
     * @return string|null
     */
    public function getEventId(): ?string
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId(string $eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    /**
     * @param string|null $taskId
     * @return $this
     */
    public function setTaskId(?string $taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentTaskId(): ?string
    {
        return $this->parentTaskId;
    }

    /**
     * @param string|null $parentTaskId
     * @return $this
     */
    public function setParentTaskId(?string $parentTaskId)
    {
        $this->parentTaskId = $parentTaskId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResultMessage(): ?string
    {
        return $this->resultMessage;
    }

    /**
     * @param string $resultMessage
     * @return $this
     */
    public function setResultMessage(string $resultMessage)
    {
        $this->resultMessage = $resultMessage;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     * @return $this
     */
    public function setHttpStatusCode(int $httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * @param string|null $responseBody
     * @return $this
     */
    public function setResponseBody(?string $responseBody)
    {
        $this->responseBody = $responseBody;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcknowledgementStatus(): ?string
    {
        return $this->acknowledgementStatus;
    }

    /**
     * @param string $acknowledgementStatus
     * @return $this
     */
    public function setAcknowledgementStatus(string $acknowledgementStatus)
    {
        $this->acknowledgementStatus = $acknowledgementStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return $this
     */
    public function setResult(string $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt(int $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
