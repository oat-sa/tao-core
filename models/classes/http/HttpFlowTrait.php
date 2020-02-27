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
 *
 */

namespace oat\tao\model\http;

use Context;
use GuzzleHttp\Psr7\Uri;
use HTTPToolkit;
use InterruptedActionException;
use oat\tao\model\routing\ActionEnforcer;
use oat\tao\model\routing\Resolver;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Trait HttpFlowTrait
 * @package oat\tao\model\http
 * @author Moyon Camille
 */
trait HttpFlowTrait
{
    /**
     * @return ServerRequestInterface
     */
    abstract public function getPsrRequest();

    /**
     * @return ServiceLocatorInterface
     */
    abstract public function getServiceLocator();

    /**
     * Redirect using the TAO FlowController implementation
     *
     * @see {@link oat\model\routing\FlowController}
     * @param string $url
     * @param int $statusCode
     * @throws InterruptedActionException
     */
    public function redirect($url, $statusCode = 302)
    {
        $context = Context::getInstance();

        header(HTTPToolkit::statusCodeHeader($statusCode));
        header(HTTPToolkit::locationHeader($url));

        throw new InterruptedActionException(
            'Interrupted action after a redirection',
            $context->getModuleName(),
            $context->getActionName()
        );
    }

    /**
     * Forward the action to execute regarding a URL
     * The forward runs into tha same HTTP request unlike redirect.
     *
     * @param string $url the url to forward to
     * @throws InterruptedActionException
     * @throws \ResolverException
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     * @throws \common_ext_ManifestNotFoundException
     */
    public function forwardUrl($url)
    {
        $uri = new Uri($url);
        $query = $uri->getQuery();
        $queryParams = [];
        if (strlen($query) > 0) {
            parse_str($query, $queryParams);
        }

        switch ($this->getPsrRequest()->getMethod()) {
            case 'GET':
                $params = $this->getPsrRequest()->getQueryParams();
                break;
            case 'POST':
                $params = $this->getPsrRequest()->getParsedBody();
                break;
            default:
                $params = [];
        }
        $request = $this->getPsrRequest()
            ->withUri($uri)
            ->withQueryParams((array) $queryParams);

        //resolve the given URL for routing
        $resolver = new Resolver($request);
        $resolver->setServiceLocator($this->getServiceLocator());

        //update the context to the new route
        $context = \Context::getInstance();
        $context->setExtensionName($resolver->getExtensionId());
        $context->setModuleName($resolver->getControllerShortName());
        $context->setActionName($resolver->getMethodName());

        $context->getRequest()->addParameters($queryParams);

        $request = $request
            ->withAttribute('extension', $resolver->getExtensionId())
            ->withAttribute('controller', $resolver->getControllerShortName())
            ->withAttribute('method', $resolver->getMethodName());

        //execute the new action
        $enforcer = new ActionEnforcer(
            $resolver->getExtensionId(),
            $resolver->getControllerClass(),
            $resolver->getMethodName(),
            $params
        );
        $enforcer->setServiceLocator($this->getServiceLocator());

        $enforcer(
            $request,
            $this->response->withHeader(
                'X-Tao-Forward',
                $resolver->getExtensionId() . '/' .  $resolver->getControllerShortName() . '/' . $resolver->getMethodName()
            )
        );

        throw new InterruptedActionException(
            'Interrupted action after a forwardUrl',
            $context->getModuleName(),
            $context->getActionName()
        );
    }

    /**
     * Forward routing.
     *
     * @param string $action the name of the new action
     * @param string $controller the name of the new controller/module
     * @param string $extension the name of the new extension
     * @param array $params additional parameters
     * @throws InterruptedActionException
     * @throws \ActionEnforcingException
     * @throws \common_exception_Error
     */
    public function forward($action, $controller = null, $extension = null, $params = [])
    {
        //as we use a route resolver, it's easier to rebuild the URL to resolve it
        $this->forwardUrl(\tao_helpers_Uri::url($action, $controller, $extension, $params));
    }
}
