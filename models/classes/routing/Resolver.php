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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\routing;

use common_http_Request;
use tao_helpers_Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use oat\tao\model\routing\NamespaceRoute;

/**
 * Resolves a http request to a controller and method
 * using the provided routers
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class Resolver implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const DEFAULT_EXTENSION = 'tao';

    /**
     * Request to be resolved
     *
     * @var common_http_Request
     */
    private $request;
    
    private $extensionId;
    
    private $controller;
    
    private $action;

    /** @var array array of available routes indexed by extension identifier */
    private static $extRoutes = [];

    /**
     * Resolves a request to a method
     * 
     * @param common_http_Request $request
     */
    public function __construct(common_http_Request $request) {
       $this->request = $request;
    }
    
    /**
     * Return the PSR7 request
     * @return ServerRequestInterface
     */
    public function getRequest() {
        return new ServerRequest(
            $this->request->getMethod(),
            $this->request->getUrl(),
            $this->request->getHeaders(),
            $this->request->getBody()
        );
    }

    /**
     * @return null
     * @throws \ResolverException
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ManifestNotFoundException
     */
    public function getExtensionId()
    {
        if ($this->extensionId === null) {
            $this->resolve();
        }
        return $this->extensionId;
    }

    /**
     * @return null
     * @throws \ResolverException
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ManifestNotFoundException
     */
    public function getControllerClass()
    {
        if ($this->controller === null) {
            $this->resolve();
        }
        return $this->controller;
    }

    /**
     * @return null
     * @throws \ResolverException
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ManifestNotFoundException
     */
    public function getMethodName()
    {
        if ($this->action === null) {
            $this->resolve();
        }
        return $this->action;
    }

    /**
     * Get the controller short name as used into the URL
     * @return string the name
     * @throws \ResolverException
     */
    public function getControllerShortName()
    {
        $relativeUrl = tao_helpers_Request::getRelativeUrl($this->request->getUrl());
        $parts = explode('/', trim($relativeUrl, '/'));
        if(count($parts) === 3){
            return $parts[1];
        }

        return null;
    }

    /**
     * Tries to resolve the current request using the routes first
     * and then falls back to the legacy controllers
     * @return bool
     * @throws \ResolverException
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ManifestNotFoundException
     */
    protected function resolve()
    {
        $extensionsManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        $installed = $extensionsManager->getInstalledExtensionsIds();
        foreach ($installed as $extId) {
            $extension = $extensionsManager->getExtensionById($extId);
            foreach ($this->getRoutes($extension) as $entry) {
                /** @var Route $route */
                $route = $entry['route'];
                $called = $route->resolve($this->getRequest());
                if ($called !== null) {
                    list($controller, $action) = explode('@', $called);
                    $this->controller = $controller;
                    $this->action = $action;
                    $this->extensionId = $entry['extId'];

                    return true;
                }
            }
        }

        throw new \ResolverException('Unable to resolve '.$this->request->getUrl());
    }

    /**
     * @param \common_ext_Extension $extension
     * @return mixed
     * @throws \common_exception_InconsistentData
     * @throws \common_ext_ManifestNotFoundException
     */
    private function getRoutes(\common_ext_Extension $extension)
    {
        $extId = $extension->getId();
        if (!isset(self::$extRoutes[$extId])) {
            $routes = [];
            foreach ($extension->getManifest()->getRoutes() as $routeId => $routeData) {
                $routes[] = [
                    'extId' => $extId,
                    'route' => $this->getRoute($extension, $routeId, $routeData)
                ];
            }
            if (empty($routes)) {
                $routes[] =[
                    'extId' => $extId,
                    'route' => new LegacyRoute($extension, $extension->getName(), [])
                ];
            }
            self::$extRoutes[$extId] = $routes;
        }
        return self::$extRoutes[$extId];
    }

    /**
     * @param \common_ext_Extension $extension
     * @param $routeId
     * @param $routeData
     * @return \oat\tao\model\routing\Route
     * @throws \common_exception_InconsistentData
     */
    private function getRoute(\common_ext_Extension $extension, $routeId, $routeData)
    {
        if (is_string($routeData)) {
            $routeData = array(
                'class' => NamespaceRoute::class,
                NamespaceRoute::OPTION_NAMESPACE => $routeData
            );
        }
        if (!isset($routeData['class']) || !is_subclass_of($routeData['class'], Route::class)) {
            throw new \common_exception_InconsistentData('Invalid route '.$routeId);
        }
        $className = $routeData['class'];
        return new $className($extension, trim($routeId, '/'), $routeData);
    }
}
