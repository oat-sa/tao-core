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
namespace oat\tao\model\routing;

use FastRoute\Dispatcher;
use oat\oatbox\service\ServiceManagerAwareTrait;
use Psr\Http\Message\ServerRequestInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Maps a relative Url to namespaced Controller class
 * Allows complex subroutes on controller level using 'route' annotation
 * Compatible with old controllers routed by NamespaceRoute
 * @see route
 *
 * @author Siarhei Baradzin
 */
class ControllerAnnotationsRoute
    extends AbstractRoute
    implements ServiceLocatorAwareInterface, PathVariablesProvidingRoute
{
    use ServiceManagerAwareTrait;

    const OPTION_NAMESPACE = 'namespace';
    const ALL_HTTP_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @var string[]
     */
    protected $pathVariables = [];

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     * @throws RouterException
     * @throws \ResolverException
     * @throws \common_exception_MethodNotAllowed
     * @throws \common_exception_ResourceNotFound
     */
    public function resolve(ServerRequestInterface $request) {
        $relativeUrl = \tao_helpers_Request::getRelativeUrl($request->getRequestTarget());
        $slash = strpos($relativeUrl, '/');

        if ($slash === false || strpos($relativeUrl, $this->getId()) !== 0) {
            return null;
        }

        $namespaceOption = $this->getConfig()[self::OPTION_NAMESPACE];
        // path after namespace part
        $rest = substr($relativeUrl, $slash + 1);
        // controller isn't specified
        if (empty($rest)) {
            return $this->resolveDefaultModule($namespaceOption);
        }

        $parts = explode('/', $rest);
        $controllerName = $parts[0];
        $controllerClass = rtrim($namespaceOption, '\\') . '\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new \common_exception_ResourceNotFound('Not Found');
        }

        // determine method which would be routed without annotations (compatibility with NamespaceRoute)
        $method = $this->getDirectMethodFromPathParts($parts);

        /** @var RouteAnnotationService $routeAnnotationService */
        $routeAnnotationService = $this->getServiceLocator()->get(RouteAnnotationService::SERVICE_ID);
        $routeInfo = $routeAnnotationService->getRoutesInfo($controllerClass);
        if (!empty($routeInfo)) {
            list($method, $this->pathVariables) =
                $this->resolveUsingAnnotations($controllerName, $request, $routeInfo, $method);
        }

        return $this->makeResultRoute($controllerClass, $method);
    }

    /**
     * @param string $namespace
     * @return string|null
     */
    private function resolveDefaultModule($namespace)
    {
        if (defined('DEFAULT_MODULE_NAME') && defined('DEFAULT_ACTION_NAME')) {
            $controller = rtrim($namespace, '\\') . '\\' . DEFAULT_MODULE_NAME;
            return $this->makeResultRoute($controller, DEFAULT_ACTION_NAME);
        }
        return null;
    }

    /**
     * @param string[] $parts
     * @return string|null
     */
    private function getDirectMethodFromPathParts(array $parts) {
        if (count($parts) > 2) {
            return null;
        }
        if (!empty($parts[1])) {
            return $parts[1];
        }

        return defined('DEFAULT_ACTION_NAME')
            ? DEFAULT_ACTION_NAME
            : null;
    }

    /**
     * @param string $controllerClass
     * @param string $method
     * @return string
     */
    private function makeResultRoute($controllerClass, $method) {
        return $controllerClass . '@' . $method;
    }

    /**
     * @param string $basePath
     * @param array $routeInfo
     * @return \Slim\Router
     */
    private function getSlimRouter($basePath, array $routeInfo) {
        $slimRouter = new \Slim\Router();

        $basePath = rtrim($basePath, '/');
        foreach ($routeInfo as $methodName => $routeAnnotation) {
            if (!is_string($methodName)) {
                continue;
            }
            $relativePath = !empty($routeAnnotation['relativePath'])
                ? ltrim($routeAnnotation['relativePath'], '/')
                : $methodName;
            $httpMethods = !empty($routeAnnotation['method'])
                ? [$routeAnnotation['method']]
                : self::ALL_HTTP_METHODS;
            $path = $basePath . '/' . $relativePath;
            $slimRouter->map($httpMethods, $path,
                static function () use ($methodName) { return $methodName; }
            );
        }

        return $slimRouter;
    }

    /**
     * @param string $partController
     * @param ServerRequestInterface $request
     * @param array $routeInfo
     * @param string|null $directMethodName
     * @return array|[string, array]
     * @throws RouterException
     * @throws \common_exception_MethodNotAllowed
     * @throws \common_exception_ResourceNotFound
     */
    private function resolveUsingAnnotations(
        $partController,
        ServerRequestInterface $request,
        array $routeInfo,
        $directMethodName = null
    ) {
        $basePath = '/' . $this->getId() . '/' . $partController;

        $slimRouter = $this->getSlimRouter($basePath, $routeInfo);

        $result = $slimRouter->dispatch($request);
        if (count($result) === 1 && $result[0] === Dispatcher::NOT_FOUND) {
            // fallback for compatibility with NamespaceRouter
            if ($directMethodName && !isset($routeInfo[$directMethodName])) {
                return [$directMethodName, []];
            }
            throw new \common_exception_ResourceNotFound('Not Found');
        }
        if (count($result) === 2 && $result[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \common_exception_MethodNotAllowed('Method Not Allowed', 0 , $result[1]);
        }
        if (count($result) === 3 && $result[0] === Dispatcher::FOUND) {
            $method = $slimRouter->getRoutes()[$result[1]]->getCallable()();
            $pathVariables = $result[2];
            return [$method, $pathVariables];
        }
        throw new RouterException('Unexpected internal error: wrong slim router response');
    }

    /**
     * @inheritdoc
     */
    public function getPathVariables()
    {
        return $this->pathVariables;
    }

    /**
     * Get controller namespace prefix
     * @return string
     */
    public static function getControllerPrefix()
    {
        return '';
    }
}
