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

namespace oat\tao\test\unit\webhooks\task;

use oat\tao\model\auth\AbstractAuthType;
use PHPUnit\Framework\MockObject\Generator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use PHPUnit\Framework\MockObject\MockObject;

class AuthTypeFake extends AbstractAuthType implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Call a request through current authenticator
     *
     * @param RequestInterface $request
     * @param array $clientOptions Http client options
     * @return ResponseInterface
     */
    public function call(RequestInterface $request, array $clientOptions = [])
    {
        $mockGenerator = new Generator();

        if (!$this->getServiceLocator()) {
            throw new \RuntimeException('Service Locator is not set');
        }

        /** @var MockObject|ResponseInterface $response */
        $response = $mockGenerator->getMock(ResponseInterface::class);
        $response->callRequest = $request;
        $response->callClientOptions = $clientOptions;
        $response->credentials = $this->credentials;

        return $response;
    }

    /**
     * RDF class or AbstractCredentials of the AuthType
     * @param array $parameters
     */
    public function getAuthClass($parameters = [])
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * All fields to configure current authenticator
     *
     * @return void
     */
    public function getAuthProperties()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Returns template for the current instance (or empty template for the default authorization) with credentials
     *
     * @return string
     */
    public function getTemplate()
    {
        throw new \RuntimeException('Not implemented');
    }
}
