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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <olexander.zagovorychev@1pt.com>
 */

namespace oat\tao\model\auth;

use GuzzleHttp\Client;
use oat\tao\helpers\Template;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Psr\Http\Message\RequestInterface;

class BasicType extends AbstractAuthType
{
    /**
     * Call a request through basic client
     *
     * @param RequestInterface $request
     * @param array $clientOptions Http client options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common_exception_InvalidArgumentType
     */
    public function call(RequestInterface $request, array $clientOptions = [])
    {
        return $this->getClient($clientOptions)->send($request, ['auth' => array_values($this->getCredentials()), 'verify' => false]);
    }

    /**
     * Returns template for the current instance (or empty template for the default authorization) with credentials
     *
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    public function getTemplate()
    {
        $data = $this->getCredentials();
        return Template::inc('auth/basicAuthForm.tpl', 'tao', $data);
    }

    /**
     * @return array|void
     */
    public function getAuthProperties()
    {
        $this->getCredentials();
    }

    /**
     * @param array $clientOptions
     * @return \core_kernel_classes_Class|AbstractCredentials|BasicAuthCredentials
     * @throws \common_exception_ValidationFailed
     */
    public function getAuthClass($clientOptions = [])
    {
        return new BasicAuthCredentials($clientOptions);
    }

    /**
     * @param $clientOptions
     * @return mixed
     */
    protected function getClient($clientOptions = [])
    {
        return new Client($clientOptions);
    }

    /**
     * @return \core_kernel_classes_Resource|void
     */
    public function getInstance()
    {
        throw new MethodNotFoundException('getInstance method was deprecated', __CLASS__, __METHOD__);
    }

    /**
     * @param \core_kernel_classes_Resource|null $instance
     */
    public function setInstance(\core_kernel_classes_Resource $instance = null)
    {
        throw new MethodNotFoundException('setInstance method was deprecated', __CLASS__, __METHOD__);
    }
}
