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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Controller
 *
 * A controller to handle http request & response following Psr7 standard
 *
 * @package oat\tao\model\http
 * @author Moyon Camille
 *
 * @deprecated Controllers shall be decoupled, using composition and Middlewares, not inheritance.
 *             They can be used with DI services (including PSR Request and Response) which will be autowired.
 *             More information here:
 *               - https://github.com/oat-sa/generis/tree/master/core/DependencyInjection
 *               - https://github.com/oat-sa/tao-core/tree/master/models/classes/routing
 */
abstract class Controller
{
    use HttpRequestHelperTrait;
    use HttpFlowTrait;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var ResponseInterface */
    protected $response;

    public function getPsrContainer(): ContainerInterface
    {
        return $this->getServiceLocator()->getContainer();
    }

    /**
     * Set Psr7 http request
     *
     * @param ServerRequestInterface $request
     * @return $this
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set Psr7 http response
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the Psr7 request
     *
     * @return ServerRequestInterface
     */
    protected function getPsrRequest()
    {
        return $this->request;
    }

    /**
     * Get the Psr7 response
     *
     * @return ResponseInterface
     */
    public function getPsrResponse()
    {
        return $this->response;
    }

    /**
     * Set cookie by setting the HTTP response header "set-cookie"
     *
     * @param $name
     * @param null $value
     * @param null $expire
     * @param null $domainPath
     * @param null $https
     * @param null $httpOnly
     * @return bool
     */
    protected function setCookie(
        $name,
        $value = null,
        $expire = null,
        $domainPath = null,
        $https = null,
        $httpOnly = null
    ) {
        return setcookie($name, $value, $expire, $domainPath, $https, $httpOnly);
    }

    /**
     * Set content-type by setting the HTTP response header "content-type"
     *
     * @param $contentType
     * @param string $charset
     * @return $this
     */
    protected function setContentHeader($contentType, $charset = 'UTF-8')
    {
        $this->response = $this->getPsrResponse()->withHeader('content-type', $contentType . ';' . $charset);
        return $this;
    }
}
