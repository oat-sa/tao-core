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

namespace oat\tao\model\webhooks\configEntity;

class Webhook implements WebhookInterface
{
    const ID = 'id';
    const URL = 'url';
    const HTTP_METHOD = 'httpMethod';
    const AUTH = 'auth';
    const RETRY_MAX = 'retryMax';
    const RESPONSE_VALIDATION = 'responseValidation';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $httpMethod;

    /**
     * @var WebhookAuth|null
     */
    private $auth;

    /**
     * @var int
     */
    private $retryMax;

    /** @var bool */
    private $responseValidation;

    /**
     * @param string $id
     * @param string $url
     * @param string $httpMethod
     * @param int $retryMax
     * @param WebhookAuth|null $auth
     * @param $responseValidation
     */
    public function __construct($id, $url, $httpMethod, $retryMax, WebhookAuth $auth = null, bool $responseValidation = true)
    {
        $this->id = $id;
        $this->url = $url;
        $this->httpMethod = $httpMethod;
        $this->auth = $auth;
        $this->retryMax = $retryMax;
        $this->responseValidation = $responseValidation;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @return WebhookAuth|null
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::ID => $this->getId(),
            self::URL => $this->getUrl(),
            self::HTTP_METHOD => $this->getHttpMethod(),
            self::AUTH => $this->getAuth() !== null
                ? $this->getAuth()->toArray()
                : null
        ];
    }

    /**
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->retryMax;
    }

    public function getResponseValidationEnable()
    {
        return $this->responseValidation;
    }
}
