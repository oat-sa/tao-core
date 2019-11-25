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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\PhpSerializable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractAuthType
 * @package oat\tao\model\auth
 */
abstract class AbstractAuthType implements PhpSerializable
{
    use OntologyAwareTrait;

    /** @var \core_kernel_classes_Resource The resource which has authorizations */
    private $instance = null;

    /** @var array  */
    protected $credentials = [];

    /**
     * Call a request through current authenticator
     *
     * @param RequestInterface $request
     * @param array $clientOptions Http client options
     * @return ResponseInterface
     * @throws \common_exception_InvalidArgumentType
     */
    abstract public function call(RequestInterface $request, array $clientOptions = []);

    /**
     * RDF class or AbstractCredentials of the AuthType
     * @param array $parameters
     * @return \core_kernel_classes_Class | AbstractCredentials
     */
    abstract public function getAuthClass($parameters = []);

    /**
     * All fields to configure current authenticator
     *
     * @return array
     */
    abstract public function getAuthProperties();
    /**
     * Returns template for the current instance (or empty template for the default authorization) with credentials
     *
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    abstract public function getTemplate();
    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\PhpSerializable::__toPhpCode()
     */
    public function __toPhpCode()
    {
        return 'new ' . get_class($this) . '()';
    }
    /**
     * @deprecated Please use setCredentials method with array of credentials for current auth type
     * Set the instance that contain authentication options
     *
     * @param \core_kernel_classes_Resource $instance
     */
    public function setInstance(\core_kernel_classes_Resource $instance = null)
    {
        $this->instance = $instance;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials($credentials = [])
    {
        $this->credentials = $credentials;
    }

    /**
     * @deprecated Please use getCredentials for getting credentials for current auth type
     * Get the instance that contain authentication options
     *
     * @return \core_kernel_classes_Resource
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        $credentialsClass = $this->getAuthClass($this->credentials);
        return $credentialsClass->getProperties();
    }
}
