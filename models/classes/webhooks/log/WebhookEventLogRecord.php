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
    private $webhookId;

    /** @var string|null */
    private $httpMethod;

    /** @var string|null */
    private $endpointUrl;

    /** @var string|null */
    private $eventName;

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
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param string|null $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResultMessage()
    {
        return $this->resultMessage;
    }

    /**
     * @param string $resultMessage
     * @return $this
     */
    public function setResultMessage($resultMessage)
    {
        $this->resultMessage = $resultMessage;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     * @return $this
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @param string|null $responseBody
     * @return $this
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcknowledgementStatus()
    {
        return $this->acknowledgementStatus;
    }

    /**
     * @param string $acknowledgementStatus
     * @return $this
     */
    public function setAcknowledgementStatus($acknowledgementStatus)
    {
        $this->acknowledgementStatus = $acknowledgementStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebhookId()
    {
        return $this->webhookId;
    }

    /**
     * @param string|null $webhookId
     */
    public function setWebhookId($webhookId)
    {
        $this->webhookId = $webhookId;
    }

    /**
     * @return string|null
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param string|null $httpMethod
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }

    /**
     * @return string|null
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * @param string|null $endpointUrl
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
    }

    /**
     * @return string|null
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param string|null $eventName
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }
}
