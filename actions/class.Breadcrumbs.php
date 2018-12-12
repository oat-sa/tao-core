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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

use oat\tao\model\routing\Resolver;
use oat\tao\model\mvc\Breadcrumbs;

/**
 * Controller that will serve breadcrumbs depending on context routes.
 * For each context route a service will be requested to get the breadcrumbs.
 *
 * To provide a breadcrumbs service you must register a class that implements `oat\tao\model\mvc\Breadcrumbs`, and
 * use a service identifier with respect to this format: `<extension>/<controller>/breadcrumbs`. Hence this service
 * will be invoked each time a breadcrumbs request is made against an action under `<extension>/<controller>`.
 * The `breadcrumbs()` method will have to provide the breadcrumb related to the route, an optionally a list of
 * related links. @see breadcrumbs() for more explanations.
 *
 * You can also override this controller and provide your own `breadcrumbs()` method, in order to provide default
 * breadcrumbs. By default there is none.
 *
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 * @package oat\tao\actions
 *
 */
class tao_actions_Breadcrumbs extends \tao_actions_CommonModule implements Breadcrumbs
{
    /**
     * Parses the provided context route
     * @param string $route
     * @return array
     */
    protected function parseRoute($route)
    {
        $parsedRoute = parse_url($route);

        if (isset($parsedRoute['query'])) {
            parse_str($parsedRoute['query'], $parsedRoute['params']);
        } else {
            $parsedRoute['params'] = [];
        }

        $resolvedRoute = new Resolver(new \common_http_Request($route));
        $this->propagate($resolvedRoute);
        $parsedRoute['extension']         = $resolvedRoute->getExtensionId();
        $parsedRoute['controller']        = $resolvedRoute->getControllerShortName();
        $parsedRoute['controller_class']  = $resolvedRoute->getControllerClass();
        $parsedRoute['action']            = $resolvedRoute->getMethodName();

        return $parsedRoute;
    }

    /**
     * Gets the provided context route
     * @return array|mixed|null|string
     * @throws common_exception_MissingParameter
     */
    protected function getRoutes()
    {
        $route = $this->getRequestParameter('route');
        if (empty($route)) {
            throw new \common_exception_MissingParameter('You must specify a route');
        }

        if (!is_array($route)) {
            $route = [$route];
        }

        return $route;
    }

    /**
     * Sends the data to the client using the preferred format
     * @param $data
     */
    protected function returnData($data)
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->returnJson([
                'success' => true,
                'data' => $data,
            ]);
        } else {
            $this->setData('breadcrumbs', $data);
            $this->setView('blocks/breadcrumbs.tpl', 'tao');
        }
    }

    /**
     * Calls a service to get the breadcrumbs for a particular route.
     * To provide a breadcrumbs service you must register a class that implements `oat\tao\model\mvc\Breadcrumbs`, and
     * use a service identifier with respect to this format: `<extension>/<controller>/breadcrumbs`. Hence this service
     * will be invoked each time a breadcrumbs request is made against an action under `<extension>/<controller>`.
     * You can also override the Breadcrumbs controller and provide your own `breadcrumbs()` method, in order to
     * provide default values. By default there is no default breadcrumbs.
     * @param string $route
     * @param array $parsedRoute
     * @return array
     * @throws common_exception_NoImplementation
     */
    protected function requestService($route, $parsedRoute)
    {
        $serviceName = null;
        if ($parsedRoute['extension'] && $parsedRoute['controller'] && $parsedRoute['action']) {
            $serviceName = $parsedRoute['extension'] . '/' . $parsedRoute['controller'] . '/breadcrumbs';
        }

        if ($serviceName && $this->getServiceLocator()->has($serviceName)) {
            $service = $this->getServiceLocator()->get($serviceName);
        } else {
            $service = $this;
        }

        if ($service instanceof Breadcrumbs) {
            return $service->breadcrumbs($route, $parsedRoute);
        } else {
            throw new common_exception_NoImplementation('Class ' . get_class($service) . ' does not implement the Breadcrumbs interface!');
        }
    }

    /**
     * Builds breadcrumbs for a particular route.
     * @param string $route - The route URL
     * @param array $parsedRoute - The parsed URL (@see parse_url), augmented with extension, controller and action
     * @return array|null - The breadcrumb related to the route, or `null` if none. Must contains:
     * - id: the route id
     * - url: the route url
     * - label: the label displayed for the breadcrumb
     * - entries: a list of related links, using the same format as above
     */
    public function breadcrumbs($route, $parsedRoute)
    {
        // default behavior: no breadcrumb
        return null;
    }

    /**
     * Loads all the breadcrumbs for a particular context route
     */
    public function load()
    {
        $data = [];
        $routes = $this->getRoutes();
        foreach($routes as $route) {
            $parsedRoute = $this->parseRoute($route);
            $routeData = $this->requestService($route, $parsedRoute);

            if ($routeData !== null) {
                // When the routeData contains more entry. (if it's a numeric array)
                if (array_values($routeData) === $routeData) {
                    $data = array_merge($data, $routeData);
                }
                else {
                    $data[] = $routeData;
                }
            }
        }
        $this->returnData($data);
    }
}
