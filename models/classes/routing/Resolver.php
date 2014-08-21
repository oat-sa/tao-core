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

use HttpRequest;
use tao_helpers_Request;
use common_ext_ExtensionsManager;

/**
 * Resolves a http request to a controller and method
 * using the provided routers
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class Resolver
{
    /**
     * Request to be resolved
     * 
     * @var HttpRequest
     */
    private $request;
    
    private $extensionId;
    
    private $controller;
    
    private $action;
    
    /**
     * Resolves a request to a method
     * 
     * @param HttpRequest $pRequest
     * @return string
     */
    public function __construct(HttpRequest $request) {
       $this->request = $request;
    }
    
    public function getExtensionId() {
        if (is_null($this->extensionId)) {
            $this->resolve();
        }
        return $this->extensionId;
    }
    
    public function getControllerClass() {
        if (is_null($this->controller)) {
            $this->resolve();
        }
        return $this->controller;
    }

    public function getMethodName() {
        if (is_null($this->action)) {
            $this->resolve();
        }
        return $this->action;
    }
    

    /**
     * Tries to resolve the current request using the routes first
     * and then falls back to the legacy controllers
     */
    protected function resolve() {
        $relativeUrl = tao_helpers_Request::getRelativeUrl($this->request->getPathUrl());
        
        $success = $this->resolveRoute($relativeUrl);
        if (!$success) {
            $success = $this->resolveLegacyClass($relativeUrl);
        }
    }
    
    /**
     * Resolves the url using the routes defined in the manifests of
     * the installed extensions
     * 
     * @param string $relativeUrl
     * @return boolean
     */
    protected function resolveRoute($relativeUrl) {
        $routes = array();
        foreach (common_ext_ExtensionsManager::singleton()->getEnabledExtensions() as $ext) {
            foreach ($ext->getManifest()->getRoutes() as $path => $routeData) {
                $path = trim($path, '/');
                if (substr($relativeUrl, 0, strlen($path)) == $path) {
                    $class = is_array($routeData) && isset($routeData['class'])
                        ? $routeData['class']
                        : 'oat\tao\model\routing\NamespaceRoute';
                    $route = new $class($path, $routeData, $relativeUrl);
                    $this->controller = $route->getControllerName();
                    $this->action = $route->getMethodName();
                    $this->extensionId = $ext->getId();
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Fallback in case no route was found
     * 
     * @param string$relativeUrl
     */
    protected function resolveLegacyClass($relativeUrl)
    {
        $parts = explode('/', $relativeUrl);
        $controllerShortName = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : DEFAULT_MODULE_NAME;
        
        $this->extensionId	= !empty($parts[0]) ? $parts[0] : 'tao';
        $this->controller   = '\\'.$this->extensionId.'_actions_'.$controllerShortName;
        $this->action		= isset($parts[2]) && !empty($parts[2]) ? $parts[2] : DEFAULT_ACTION_NAME;
    }
}
