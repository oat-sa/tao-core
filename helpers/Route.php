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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\helpers;

use oat\tao\model\routing\LegacyRoute;
use oat\tao\model\routing\NamespaceRoute;

/**
 * Class Route
 * @package oat\tao\helpers
 */
class Route
{
    /**
     * Tries to resolve an URL using the routes first
     * and then falls back to the legacy controllers
     * 
     * @param string $url
     * @return array
     * @throws \ResolverException
     */
    public static function resolve($url)
    {
        $parsedRoute = parse_url($url);
        if (isset($parsedRoute['query'])) {
            parse_str($parsedRoute['query'], $parsedRoute['params']);
        } else {
            $parsedRoute['params'] = [];
        }

        $relativeUrl = \tao_helpers_Request::getRelativeUrl($url);
        foreach (self::getRouteMap() as $entry) {
            $route = $entry['route'];
            $called = $route->resolve($relativeUrl);
            if (!is_null($called)) {
                list($controllerClass, $action) = explode('@', $called);
                $controllerNS = explode('\\', $controllerClass);
                $controller = $controllerNS[count($controllerNS) - 1]; 

                $parsedRoute['extension'] = $entry['extId'];
                $parsedRoute['controller'] = $controller;
                $parsedRoute['controller_class'] = $controllerClass;
                $parsedRoute['action'] = $action;
                
                return $parsedRoute;
            }
        }
        throw new \ResolverException('Unable to resolve ' . $url);
    }

    /**
     * @param \common_ext_Extension $extension
     * @return array
     * @throws \common_exception_InconsistentData
     */
    private static function getRoutes(\common_ext_Extension $extension)
    {
        $routes = array();
        foreach ($extension->getManifest()->getRoutes() as $routeId => $routeData) {
            if (is_string($routeData)) {
                $routeData = array(
                    'class' => 'oat\\tao\\model\\routing\\NamespaceRoute',
                    NamespaceRoute::OPTION_NAMESPACE => $routeData
                );
            }
            if (!isset($routeData['class']) || !is_subclass_of($routeData['class'], 'oat\tao\model\routing\Route')) {
                throw new \common_exception_InconsistentData('Invalid route ' . $routeId);
            }
            $className = $routeData['class'];
            $routes[] = new $className($extension, trim($routeId, '/'), $routeData);
        }
        if (empty($routes)) {
            $routes[] = new LegacyRoute($extension, $extension->getName(), array());
        }
        return $routes;
    }

    /**
     * @return array
     */
    private static function getRouteMap()
    {
        $routes = array();
        foreach (\common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
            foreach (self::getRoutes($extension) as $route) {
                $routes[] = array(
                    'extId' => $extension->getId(),
                    'route' => $route
                );
            }
        }
        return $routes;
    }
}
