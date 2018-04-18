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
use Psr\Http\Message\RequestInterface;

class BasicAuthType extends AbstractAuthType implements BasicAuth
{
    /**
     * Call a request through basic client
     *
     * @param RequestInterface $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \common_exception_InvalidArgumentType
     */
    public function call(RequestInterface $request)
    {
        return (new Client())->send($request, ['auth' => $this->getCredentials(), 'verify' => false]);
    }

    /**
     * RDF class of the AuthType
     *
     * @return \core_kernel_classes_Class
     */
    public function getAuthClass()
    {
        return $this->getClass(self::CLASS_BASIC_AUTH);
    }

    /**
     * All fields to configure current authenticator
     *
     * @return array Array of properties of login/password
     */
    public function getAuthProperties()
    {
        return [
            $this->getProperty(self::PROPERTY_LOGIN),
            $this->getProperty(self::PROPERTY_PASSWORD),
        ];
    }

    /**
     * Returns template for the current instance (or empty template for the default authorization) with credentials
     *
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    public function getTemplate()
    {
        $data = $this->loadCredentials();
        return Template::inc('auth/basicAuthForm.tpl', 'tao', $data);
    }

    /**
     * Fetch the credentials for the current resource.
     *
     * Contains login and password or with null value if empty
     *
     * @return array
     * @throws \common_exception_InvalidArgumentType
     */
    protected function loadCredentials()
    {
        $instance = $this->getInstance();
        if ($instance && $instance->exists()) {

            $props = $instance->getPropertiesValues([
                $this->getProperty(self::PROPERTY_LOGIN),
                $this->getProperty(self::PROPERTY_PASSWORD)
            ]);

            $data = [
                self::PROPERTY_LOGIN => (string)current($props[self::PROPERTY_LOGIN]),
                self::PROPERTY_PASSWORD => (string)current($props[self::PROPERTY_PASSWORD]),
            ];
        } else {
            $data = [
                self::PROPERTY_LOGIN => '',
                self::PROPERTY_PASSWORD => '',
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \common_exception_InvalidArgumentType
     */
    protected function getCredentials()
    {
        $credentials = $this->loadCredentials();
        return [
            $credentials[self::PROPERTY_LOGIN],
            $credentials[self::PROPERTY_PASSWORD],
        ];
    }
}
