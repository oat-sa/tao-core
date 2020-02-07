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

namespace oat\tao\model\webhooks\task;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\auth\AbstractAuthType;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class WebhookSender extends ConfigurableService
{
    /**
     * @param RequestInterface $request
     * @param WebhookAuthInterface|null $authConfig
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ClientException
     * @throws \InvalidArgumentException
     * @throws \common_exception_InvalidArgumentType
     */
    public function performRequest(RequestInterface $request, WebhookAuthInterface $authConfig = null)
    {
        return $authConfig
            ? $this->performRequestWithAuth($request, $authConfig)
            : $this->preformRequestWithoutAuth($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function preformRequestWithoutAuth(RequestInterface $request)
    {
        $client = new Client();
        return $client->send($request);
    }

    /**
     * @param RequestInterface $request
     * @param WebhookAuthInterface $authConfig
     * @return ResponseInterface
     * @throws \common_exception_InvalidArgumentType
     */
    private function performRequestWithAuth(RequestInterface $request, WebhookAuthInterface $authConfig)
    {
        $authClass = $authConfig->getAuthClass();
        if (!class_exists($authClass) || !is_subclass_of($authClass, AbstractAuthType::class)) {
            throw new \InvalidArgumentException("Auth class '$authClass' is not " . AbstractAuthType::class);
        }

        /** @var AbstractAuthType $authImpl */
        $authImpl = new $authClass();
        if ($authImpl instanceof ServiceLocatorAwareInterface) {
            $authImpl->setServiceLocator($this->getServiceLocator());
        }
        $authImpl->setCredentials($authConfig->getCredentials());

        return $authImpl->call($request);
    }
}
